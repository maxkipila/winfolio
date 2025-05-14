const puppeteer = require("puppeteer-extra");
const StealthPlugin = require("puppeteer-extra-plugin-stealth");
const fs = require("fs");

puppeteer.use(StealthPlugin());

// Definice debug proměnné z prostředí
const isDebug = process.env.DEBUG === 'true';

(async () => {
    const url = process.argv[2] || '';
    
    if (!url) {
        console.log(JSON.stringify({ error: "URL není zadána" }));
        process.exit(1);
    }

    const args = ["--no-sandbox", "--disable-setuid-sandbox"];
    

    const browser = await puppeteer.launch({
        headless: true,
        args: args,
    });

    try {
        const page = await browser.newPage();

        await page.setUserAgent("Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/16.5.1 Safari/605.1.15");

        await page.setExtraHTTPHeaders({
            "Accept-Language": "en-US,en;q=0.9,cs;q=0.8",
            "Accept": "text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8",
            "Accept-Encoding": "gzip, deflate, br",
            "Cache-Control": "no-cache",
            "Pragma": "no-cache"
        });

        if (isDebug) console.error("DEBUG: Navštěvuji URL:", url);
        await page.goto(url, { waitUntil: "networkidle2", timeout: 30000 });

        // Čekání na načtení stránky
        await new Promise((resolve) => setTimeout(resolve, 5000));

        // Uložení HTML pro debug
        if (isDebug) {
            const htmlContent = await page.content();
            console.error("DEBUG: HTML Content Length:", htmlContent.length);
            fs.writeFileSync('debug_page.html', htmlContent);
            console.error("DEBUG: HTML uložen do debug_page.html");
        }

        const data = await page.evaluate(() => {
            const result = {
                name: null,
                value: null,
                retail: null,
                condition: "New",
                currency: "EUR",
                availability: null,
                img_url: null,
                theme_name: null,
            };

            // Lokální debug uvnitř evaluate
            const debug = false; 

            // Jméno produktu
            const nameElement = document.querySelector("h1");
            if (nameElement) {
                result.name = nameElement.innerText.trim();
                if (debug) console.log("DEBUG: Název setu:", result.name);
            }

            // Vylepšení detekce obrázků - zkusíme několik selektorů
            const imgSelectors = [
                "img.img-thumbnail",                 // Běžný obrázek produktu
                ".product-image img",                // Alternativní selektor
                ".set-image img",                    // Pro sety
                ".minifig-image img",                // Pro minifigurky
                "#ContentPlaceHolder1_imgItem",      // Specifický ID selektor
                ".item-img img",                     // Obecný selektor obrázku položky
            ];

            // Projdeme všechny selektory a použijeme první nalezený
            for (const selector of imgSelectors) {
                const imgEl = document.querySelector(selector);
                if (imgEl && imgEl.src) {
                    // Zkontrolujeme, že URL není prázdná a není to placeholder
                    if (imgEl.src && 
                        !imgEl.src.includes('placeholder') && 
                        !imgEl.src.includes('no-image')) {
                        
                        // Pokud URL neobsahuje protokol, přidáme ho
                        result.img_url = imgEl.src.startsWith('http') 
                            ? imgEl.src 
                            : 'https:' + imgEl.src;
                        
                        if (debug) console.log("DEBUG: Obrázek nalezen:", result.img_url);
                        break;
                    }
                }
            }
            
            // Pokud stále nemáme obrázek, zkusíme najít jakýkoliv obrázek s rozumnou velikostí
            if (!result.img_url) {
                const allImages = document.querySelectorAll('img');
                
                for (const img of allImages) {
                    // Přeskočíme malé obrázky a ikony
                    if (img.width >= 100 && img.height >= 100 && img.src) {
                        result.img_url = img.src.startsWith('http') 
                            ? img.src 
                            : 'https:' + img.src;
                        if (debug) console.log("DEBUG: Alternativní obrázek nalezen:", result.img_url);
                        break;
                    }
                }
            }

            // Hledání availability - OPRAVENO
            const bodyText = document.body.innerText;
            const availabilityRegex = /Availability\s*:?\s*([^\n]+)/i;
            const availabilityMatch = bodyText.match(availabilityRegex);
            if (availabilityMatch && availabilityMatch[1]) {
                result.availability = availabilityMatch[1].trim();
                if (debug) console.log("DEBUG: Availability from regex:", result.availability);
            }

            if (!result.availability) {
                document.querySelectorAll('.set-details .rowlist').forEach(row => {
                    const rowText = row.innerText;
                    if (rowText.toLowerCase().includes('availability')) {
                        const parts = rowText.split(':');
                        if (parts.length > 1) {
                            result.availability = parts[1].trim();
                            if (debug) console.log("DEBUG: Availability from rows:", result.availability);
                        }
                    }
                });
            }

            // Retail price - OPRAVENO
            const retailRegex = /Retail price\s*:?\s*[€$]?(\d+\.?\d*)/i;
            const retailMatch = bodyText.match(retailRegex);
            if (retailMatch && retailMatch[1]) {
                result.retail = parseFloat(retailMatch[1]);
                if (debug) console.log("DEBUG: Retail price from regex:", result.retail);
            }

            // Value
            const pricingPanel = document.querySelector("#ContentPlaceHolder1_PanelSetPricing .side-box-body");
            if (pricingPanel) {
                const rows = pricingPanel.querySelectorAll(".row.rowlist");
                if (debug) console.log("DEBUG: Pricing panel rows:", rows.length);

                rows.forEach(row => {
                    const labelElement = row.querySelector(".col-xs-5") || row.querySelector(".label");
                    const valueElement = row.querySelector(".col-xs-7 b") || row.querySelector("strong");

                    if (labelElement && valueElement) {
                        const label = labelElement.innerText.trim().toLowerCase();
                        const valueText = valueElement.innerText.trim();
                        if (debug) console.log("DEBUG: Price label:", label, "Value:", valueText);

                        const numericValue = parseFloat(valueText.replace(/[^0-9.]/g, ""));
                        if (!isNaN(numericValue)) {
                            if (label.includes("value")) {
                                result.value = numericValue;
                                if (debug) console.log("DEBUG: Found value:", result.value);
                            } else if (label.includes("retail price") && !result.retail) {
                                result.retail = numericValue;
                                if (debug) console.log("DEBUG: Found retail price:", result.retail);
                            }
                        }
                    }
                });
            }

            // Theme
            const themeElement = Array.from(document.querySelectorAll('.col-xs-5')).find(el =>
                el.innerText.trim().toLowerCase() === 'theme'
            );
            if (themeElement) {
                const valueElement = themeElement.nextElementSibling;
                if (valueElement && valueElement.classList.contains('col-xs-7')) {
                    result.theme_name = valueElement.innerText.trim();
                    if (debug) console.log("DEBUG: Theme found:", result.theme_name);
                }
            }

            return result;
        });

        console.log(JSON.stringify(data));
    } catch (error) {
        console.log(JSON.stringify({ error: error.message }));
    } finally {
        await browser.close();
    }
})();