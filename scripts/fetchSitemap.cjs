const { execSync } = require("child_process");

(async () => {
    // scripts/fetchHtml.cjs
    const puppeteer = require("puppeteer-extra");
    const StealthPlugin = require("puppeteer-extra-plugin-stealth");
    puppeteer.use(StealthPlugin());
    const fs = require("fs");
    const { execSync } = require("child_process");

    (async () => {
        const proxy = process.argv[2];
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

            const response = await page.goto(
                "https://www.brickeconomy.com/sitemap.xml",
                {
                    waitUntil: "networkidle2",
                    timeout: 0,
                }
            );

            let success = false;
            const maxRetries = 3;
            let attempt = 0;

            while (!success && attempt < maxRetries) {
                try {
                    await page.waitForFunction(
                        () =>
                            document.documentElement.innerHTML.includes(
                                "<urlset"
                            ) ||
                            document.documentElement.innerHTML.includes(
                                "<sitemapindex"
                            ),
                        { timeout: 7000 }
                    );

                    success = true;
                } catch (error) {
                    attempt++;

                    if (attempt < maxRetries) {
                        await page.reload({ waitUntil: "networkidle2" });
                    }
                }
            }

            if (success) {
                const xml = await page.evaluate(async () => {
                    const res = await fetch(
                        "https://www.brickeconomy.com/sitemap.xml"
                    );
                    return await res.text();
                });

                if (xml) {
                    fs.writeFileSync(`storage/app/html/sitemap.xml`, xml);
                }
            }
        } catch (error) {
            await browser.close();
        }

        await browser.close();
    })();
})();
