// scripts/fetchHtml.cjs
const puppeteer = require("puppeteer-extra");
const StealthPlugin = require("puppeteer-extra-plugin-stealth");
puppeteer.use(StealthPlugin());

(async () => {
    const url = process.argv[2];
    const proxy = process.argv[3];
    // Extract proxy host (without credentials) for --proxy-server

    const proxyUrl = new URL(proxy);
    const proxyHost = `${proxyUrl.hostname}:${proxyUrl.port}`;

    const launchArgs = [`--no-sandbox`, `--proxy-server=${proxyHost}`];

    const browser = await puppeteer.launch({ args: launchArgs });
    const page = await browser.newPage();

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

    if (response?.status() != 200) {
        if (response?.status() == 429 || response?.status() == 403) {
            const timestamp = new Date();
            const formatted = timestamp.toISOString().replace(/[:.]/g, "-");

            let success = false;
            const maxRetries = 3;
            let attempt = 0;

            while (!success && attempt < maxRetries) {
                try {
                    await page.waitForSelector(
                        "#ContentPlaceHolder1_PanelSetPricing",
                        { timeout: 7500 }
                    );
                    success = true;
                } catch (error) {
                    attempt++;
                    if (attempt < maxRetries) {
                        await page.reload({ waitUntil: "networkidle2" });
                    } else {
                        await page.screenshot({
                            path: `storage/app/error_screenshot-${formatted}.png`,
                            fullPage: true,
                        });
                        await browser.close();
                        throw new Error(
                            `Failed to find selector after ${maxRetries} attempts`
                        );
                    }
                }
            }

            await page.screenshot({
                path: `storage/app/success_${attempt}_screenshot-${formatted}.png`,
                fullPage: true,
            });
        }
    }

    console.log(await page.content());
    await browser.close();
})();
