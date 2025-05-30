// scripts/fetchHtml.cjs
const puppeteer = require("puppeteer-extra");
const StealthPlugin = require("puppeteer-extra-plugin-stealth");
puppeteer.use(StealthPlugin());
const fs = require("fs");
const { execSync } = require("child_process");

(async () => {
    const urls = JSON.parse(process.argv[2]);
    const proxy = process.argv[3];
    // Extract proxy host (without credentials) for --proxy-server

    const proxyUrl = new URL(proxy);
    const proxyHost = `${proxyUrl.hostname}:${proxyUrl.port}`;

    const launchArgs = [`--no-sandbox`, `--proxy-server=${proxyHost}`];

    const browser = await puppeteer.launch({
        args: launchArgs,
        protocolTimeout: 120000,
    });
    const page = await browser.newPage();

    const successful = 0;

    try {
        for (const [product_id, url] of Object.entries(urls)) {
            const loopStart = Date.now();
            await page.setCookie({
                name: "Region",
                value: "US",
                domain: "www.brickeconomy.com",
                path: "/",
                httpOnly: false,
                secure: true,
                sameSite: "Lax",
            });

            // If proxy requires authentication
            if (proxyUrl.username && proxyUrl.password) {
                await page.authenticate({
                    username: proxyUrl.username,
                    password: proxyUrl.password,
                });
            }

            const response = await page.goto(url, {
                waitUntil: "networkidle2",
                timeout: 0,
            });

            let success = false;
            const maxRetries = 3;
            let attempt = 0;

            while (!success && attempt < maxRetries) {
                const timestamp = new Date();
                const formatted = timestamp.toISOString().replace(/[:.]/g, "-");

                try {
                    await page.waitForSelector(
                        "#ContentPlaceHolder1_PanelSetPricing, #ContentPlaceHolder1_PanelMinifigPricing",
                        { timeout: 7500 }
                    );

                    success = true;
                } catch (error) {
                    attempt++;

                    if (attempt < maxRetries) {
                        const errorSelector = "h3.text-center.mt-20";
                        const errorElement = await page.$(errorSelector);

                        if (errorElement) {
                            const errorText = await page.evaluate(
                                (el) => el.textContent,
                                errorElement
                            );
                            if (
                                errorText &&
                                errorText.includes(
                                    "The page you are looking for could not be found."
                                )
                            ) {
                                // await page.screenshot({
                                //     path: `storage/app/screenshots/not_found_${attempt}_${product_id}-${formatted}.png`,
                                //     fullPage: true,
                                // });

                                // throw new Error("Page not found error detected!");
                                execSync(
                                    `php artisan log:error ${product_id} "Page not found error detected!" 404`
                                );
                                break;
                            }
                        }

                        await page.reload({ waitUntil: "networkidle2" });
                    } else {
                        await page.screenshot({
                            path: `storage/app/screenshots/error_${attempt}_${product_id}-${formatted}.png`,
                            fullPage: true,
                        });

                        // throw new Error(
                        //     `Failed to find selector after ${maxRetries} attempts`
                        // );

                        execSync(
                            `php artisan log:error ${product_id} "Failed to find selector after ${maxRetries} attempts" 4290`
                        );
                    }
                }
            }

            if (success) {
                if (attempt > 0) {
                    await page.screenshot({
                        path: `storage/app/screenshots/success_${attempt}_${product_id}-${formatted}.png`,
                        fullPage: true,
                    });
                }

                const content = await page.content();

                if (content) {
                    fs.writeFileSync(
                        `storage/app/html/tmp_scraped_${product_id}.html`,
                        await page.content()
                    );

                    try {
                        execSync(
                            `php artisan process:scraped-pages storage/app/html/tmp_scraped_${product_id}.html ${product_id}`
                        );

                        successful++;
                    } catch (error) {}
                } else {
                    execSync(
                        `php artisan log:error ${product_id} "Page returned no content" 204`
                    );
                }
            }
            const loopEnd = Date.now();
            const durationSeconds = ((loopEnd - loopStart) / 1000).toFixed(2);
            execSync(
                `php artisan log:error ${product_id} "Scrape took ${durationSeconds} s" 999`
            );
        }
    } catch (error) {
        execSync(
            `php artisan log:error ${0} "Some error occured" 500 "${Buffer.from(
                JSON.stringify({ error: error, urls: urls })
            ).toString("base64")}"`
        );
        await browser.close();
    }

    console.log(successful);
    await browser.close();
})();
