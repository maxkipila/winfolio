<?php

namespace App\Traits;


use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Nesk\Puphpeteer\Puppeteer;
use Nesk\Rialto\Data\JsFunction;
use Nesk\Rialto\Exceptions\Node\FatalException;
use Symfony\Component\Process\Process;

trait HasUserAgent
{
    public $userAgents = [
        // Chrome (Windows)
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36',
        // Chrome (Mac)
        'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36',
        // Chrome (Linux)
        'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36',
        // Firefox (Windows)
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:126.0) Gecko/20100101 Firefox/126.0',
        // Firefox (Mac)
        'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:126.0) Gecko/20100101 Firefox/126.0',
        // Firefox (Linux)
        'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:126.0) Gecko/20100101 Firefox/126.0',
        // Edge (Windows)
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36 Edg/124.0.2478.80',
        // Safari (Mac)
        'Mozilla/5.0 (Macintosh; Intel Mac OS X 13_4) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.4 Safari/605.1.15',
        // Safari (iPhone)
        'Mozilla/5.0 (iPhone; CPU iPhone OS 17_4 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.4 Mobile/15E148 Safari/604.1',
        // Safari (iPad)
        'Mozilla/5.0 (iPad; CPU OS 17_4 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.4 Mobile/15E148 Safari/604.1',
        // Chrome (Android)
        'Mozilla/5.0 (Linux; Android 14; Pixel 7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Mobile Safari/537.36',
        // Samsung Internet (Android)
        'Mozilla/5.0 (Linux; Android 14; SM-G998B) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/24.0 Chrome/124.0.0.0 Mobile Safari/537.36',
        // Opera (Windows)
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36 OPR/109.0.0.0',
        // Opera (Mac)
        'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36 OPR/109.0.0.0',
        // Brave (Windows)
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36 Brave/1.65.132',
        // Brave (Mac)
        'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36 Brave/1.65.132',
        // Vivaldi (Windows)
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36 Vivaldi/6.7.3329.31',
        // Vivaldi (Mac)
        'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36 Vivaldi/6.7.3329.31',
        // Internet Explorer 11 (Windows)
        'Mozilla/5.0 (Windows NT 10.0; WOW64; Trident/7.0; rv:11.0) like Gecko',
        // Googlebot
        'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)',
        // Bingbot
        'Mozilla/5.0 (compatible; bingbot/2.0; +http://www.bing.com/bingbot.htm)',
        // YandexBot
        'Mozilla/5.0 (compatible; YandexBot/3.0; +http://yandex.com/bots)',
    ];

    function proxyRequest($urls)
    {

        // $API_KEY = env('PROXY_API_KEY');
        $API_CREDENTIALS = env('PROXY_CREDENTIALS');
        // $cacheKey = 'api_data_' . md5("https://proxy-seller.com/personal/api/v1/{$API_KEY}/proxy/download/resident?listId=12268929"); // Unique cache key for the URL
        // $ttl = 60; // Cache for 1 hour (in seconds)

        // $proxy = Cache::remember($cacheKey, $ttl, function () use ($API_KEY) {
        //     return Http::get("https://proxy-seller.com/personal/api/v1/{$API_KEY}/proxy/download/resident?listId=12268929")->body();
        // });

        $process = new Process(['node', base_path('scripts/fetchHtml.cjs'), json_encode($urls), "https://{$API_CREDENTIALS}@217.30.10.33:43587"]);
        $process->setTimeout(300);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new \Exception($process->getErrorOutput());
        }

        $html = $process->getOutput();

        // $html = Http::withOptions([
        //     'proxy' => "maxkipila:niAh8JrkDz@217.30.10.33:43587"
        // ])
        //     ->withHeaders([
        //         'User-Agent' => $this->userAgents[rand(0, count($this->userAgents) - 1)],
        //         'Accept' => 'text/html,application/xhtml+xml,application/xml',
        //         'Accept-Language' => 'en-US,en;q=0.9',
        //     ])->get($url);

        // $filePath = storage_path('app/fetch_output.html');
        // file_put_contents($filePath, $html);

        // dd("Saved HTML");
        // return $process->getOutput();
        return $html;
    }


    function puppeteerRequest($urls = [])
    {
        $API_CREDENTIALS = env('PROXY_CREDENTIALS');
        $proxy = "https://{$API_CREDENTIALS}@217.30.10.33:43587";

        // Parse proxy
        $proxyUrl = parse_url($proxy);
        $proxyHost = $proxyUrl['host'] . ':' . $proxyUrl['port'];
        $proxyAuth = isset($proxyUrl['user'], $proxyUrl['pass']) ? $proxyUrl['user'] . ':' . $proxyUrl['pass'] : null;

        $launchArgs = [
            '--no-sandbox',
            '--proxy-server=' . $proxyHost,
        ];

        $puppeteer = new Puppeteer(
            [
                'js_extra' =>
                /** @lang JavaScript */
                "
            const puppeteer = require('puppeteer-extra');
            const StealthPlugin = require('puppeteer-extra-plugin-stealth');
            puppeteer.use(StealthPlugin());
            instruction.setDefaultResource(puppeteer);
        "
            ]
        );

        $browser = $puppeteer->launch([
            'args' => $launchArgs,
            'headless' => true,
        ]);

        $pages = [];
        try {
            $page = $browser->tryCatch->newPage();

            foreach ($urls as $product_id => $url) {
                try {
                    Log::info("Scraping $product_id => $url");

                    // Set cookie
                    $page->tryCatch->setCookie([
                        'name' => 'Region',
                        'value' => 'US',
                        'domain' => 'www.brickeconomy.com',
                        'path' => '/',
                        'httpOnly' => false,
                        'secure' => true,
                        'sameSite' => 'Lax',
                    ]);

                    // Proxy authentication if needed
                    if ($proxyAuth) {
                        [$username, $password] = explode(':', $proxyAuth, 2);
                        $page->tryCatch->authenticate([
                            'username' => $username,
                            'password' => $password,
                        ]);
                    }
                    Log::info("Going to $url");
                    $response = $page->tryCatch->goto($url, [
                        'waitUntil' => 'networkidle2',
                        'timeout' => 0,
                    ]);

                    $status = $response?->status() ?? NULL;

                    try {
                        if ($status !== 200) {
                            if ($status == 429 || $status == 403 || $status == NULL) {
                                $timestamp = now()->format('Y-m-d_H-i-s');
                                $success = false;
                                $maxRetries = 3;
                                $attempt = 0;

                                Log::info("Got 429 or 403 on $url");

                                while (!$success && $attempt < $maxRetries) {
                                    try {
                                        $page->tryCatch->waitForSelector('#ContentPlaceHolder1_PanelSetPricing', ['timeout' => 7500]);
                                        $success = true;
                                    } catch (\Exception $e) {
                                        $attempt++;
                                        if ($attempt < $maxRetries) {
                                            // Check for not found error
                                            $errorSelector = 'h3.text-center.mt-20';
                                            try {
                                                $errorElement = $page->tryCatch->querySelector($errorSelector);
                                                if ($errorElement) {
                                                    $errorText = $page->tryCatch->evaluate(JsFunction::createWithBody('el => el.textContent'), [$errorElement]);
                                                    if (strpos($errorText, 'The page you are looking for could not be found.') !== false) {
                                                        $page->tryCatch->screenshot([
                                                            'path' => "storage/app/screenshots/not_found_screenshot-{$timestamp}.png",
                                                            'fullPage' => true,
                                                        ]);
                                                        // throw new Exception('Page not found error detected!');
                                                        Log::warning('Page not found error detected!', ['product_id' => $product_id]);
                                                    }
                                                }
                                                $page->tryCatch->reload(['waitUntil' => 'networkidle2']);
                                            } catch (\Throwable $th) {
                                                Log::error($th->getMessage(), [$th]);
                                            }
                                        } else {
                                            $page->tryCatch->screenshot([
                                                'path' => "storage/app/screenshots/error_screenshot-{$timestamp}.png",
                                                'fullPage' => true,
                                            ]);
                                            // throw new Exception("Failed to find selector after {$maxRetries} attempts");
                                            Log::warning("Failed to find selector after {$maxRetries} attempts", ['product_id' => $product_id]);
                                        }
                                    }
                                }
                                Log::info("Done trying for $url");
                                if ($success) {
                                    try {
                                        $page->tryCatch->screenshot([
                                            'path' => "storage/app/screenshots/success_{$attempt}_screenshot-{$timestamp}.png",
                                            'fullPage' => true,
                                        ]);
                                    } catch (\Throwable $th) {
                                        Log::error($th->getMessage(), [$th]);
                                    }
                                }
                            }
                        }
                    } catch (\Throwable $th) {
                        Log::error($th->getMessage(), [$th]);
                    }
                } catch (\Throwable $th) {
                    Log::error($th->getMessage(), [$th]);
                }

                try {
                    $content = $page->tryCatch->content();

                    if ($content) {
                        Log::info("Adding page to array $product_id => $url");
                        $pages[$product_id] = $content;
                    }
                } catch (\Throwable $th) {
                    Log::error($th->getMessage(), [$th]);
                }
            }
        } catch (FatalException $e) {
            Log::error($e->getMessage(), [$e]);
        }

        $browser->close();

        return $pages;
    }
}
