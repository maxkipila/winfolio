<?php

namespace App\Services;

use App\Models\LegoIdMapping;
use App\Models\Product;
use App\Models\Price;
use Exception;
use GuzzleHttp\Client;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class BrickScraperService
{
    protected Client $rebrickableClient;
    protected Client $brickEconomyClient;

    public function __construct()
    {
        $this->initializeClients();
    }

    protected function initializeClients()
    {
        $this->rebrickableClient = new Client([
            'headers' => [
                'User-Agent' => 'Mozilla/5.0...',

            ],
            'timeout' => 30,
            'verify' => false, // V produkci by mělo být true
        ]);

        $this->brickEconomyClient = new Client([
            'headers' => [
                'User-Agent' => 'Mozilla/5.0...',

            ],
            'timeout' => 30,
            'verify' => false, // V produkci by mělo být true
        ]);
    }

    // Jednotná metoda pro získání dat z BrickEconomy
    public function scrapeBrickEconomy(string $productNum, bool $saveToDb = true): ?array
    {
        // Kód z původního BrickEconomyScraper
    }

    // Metoda pro mapování ID mezi systémy
    public function mapProductId(string $rebrickableId): ?string
    {
        // Mapování ID
    }

    // Jednotná metoda pro ukládání produktů
    public function saveProductToDatabase(array $productData): ?Product
    {
        // Ukládání dat
    }

    // Jednotná metoda pro ukládání cen
    public function savePriceData(Product $product, array $priceData): ?Price
    {
        // Ukládání cenových dat
    }
}
