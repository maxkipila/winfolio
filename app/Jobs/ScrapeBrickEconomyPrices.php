<?php

namespace App\Jobs;

use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ScrapeBrickEconomyPrices implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(public $product_id)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {

        $product_id = $this->product_id;
        $product = Product::find($this->product_id);

        if (!$product)
            return;

        $url = "https://www.brickeconomy.com/{$product->product_type}/{$product->brickeconomy_id}/";

        try {
            $response = Http::withCookies([
                'Region' => 'US',
            ], 'www.brickeconomy.com')
                ->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36',
                    'Accept' => 'text/html,application/xhtml+xml,application/xml',
                    'Accept-Language' => 'en-US,en;q=0.9',
                ])
                ->get($url);
        } catch (\Exception $e) {
            Log::error($e->getMessage(), ['product_id' => $product_id, 'brickeconomy_id' => $product?->brickeconomy_id]);
        }

        $html = $response->body();

        preg_match_all('/data\.addRows\(\[\s*(.*?)\s*\]\);/s', $html, $rowsMatches, PREG_SET_ORDER);
        $dataRows = '';

        foreach ($rowsMatches as $match) {
            $rowsContent = $match[1];
            if (strpos($rowsContent, "'Released'") !== false) {
                $dataRows = $rowsContent;
                break;
            }
        }

        if (!$dataRows && !empty($rowsMatches)) {
            $dataRows = $rowsMatches[0][1] ?? '';
        }

        if ($dataRows && preg_match_all('/\[new Date\((\d+),\s*(\d+),\s*(\d+)\),\s*([\d.]+)/s', $dataRows, $matches, PREG_SET_ORDER)) {
            $minYear = 1970;
            $maxYear = now()->year;
            foreach ($matches as $match) {
                $year = (int)$match[1];
                $month = (int)$match[2];
                $day = (int)$match[3];
                $price = (float)$match[4];

                if ($year >= $minYear && $year <= $maxYear) {
                    $date = Carbon::create($year, $month, $day)->format('Y-m-d');
                    $product->prices()->create([
                        'date' => $date,
                        'value' => $price,
                        'currency' => 'USD',
                        'type' => 'Aggregated'
                    ]);
                }
            }
        }
    }
}
