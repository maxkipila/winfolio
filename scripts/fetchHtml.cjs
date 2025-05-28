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

    // If proxy requires authentication
    if (proxyUrl.username && proxyUrl.password) {
        await page.authenticate({
            username: proxyUrl.username,
            password: proxyUrl.password,
        });
    }

    await page.goto(url, { waitUntil: "networkidle2" });

    if (page.response.status() != 200) {
        await browser.close();
        throw new Error(`Non-200 status code: ${response.status()}`);
    }

    console.log(await page.content());
    await browser.close();
})();
