const { execSync } = require("child_process");

(async () => {
    // scripts/fetchHtml.cjs
    const puppeteer = require("puppeteer-extra");
    const StealthPlugin = require("puppeteer-extra-plugin-stealth");
    puppeteer.use(StealthPlugin());
    const fs = require("fs");
    const { execSync } = require("child_process");

    (async () => {
        const url = process.argv[2];
        const theme = process.argv[3];
        const proxy = process.argv[4];
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
                try {
                    await page.waitForSelector(".container.theme-showcase", {
                        timeout: 7500,
                    });

                    success = true;
                } catch (error) {
                    attempt++;

                    if (attempt < maxRetries) {
                        await page.reload({ waitUntil: "networkidle2" });
                    }
                }
            }

            if (success) {
                const content = await page.content();

                if (content) {
                    fs.writeFileSync(
                        `storage/app/html/tmp_theme_${theme}.html`,
                        await page.content()
                    );

                    try {
                        execSync(
                            `php artisan process:scraped-themes storage/app/html/tmp_theme_${theme}.html ${theme}`
                        );
                    } catch (error) {}
                }
            }
        } catch (error) {
            await browser.close();
        }

        await browser.close();
    })();
})();
