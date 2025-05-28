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
            await page.screenshot({
                path: `storage/app/error_screenshot-${formatted}.png`,
                fullPage: true,
            });
        }

        await browser.close();
        throw new Error(`Non-200 status code: ${response?.status()}`);
    }

    console.log(await page.content());
    await browser.close();
})();
