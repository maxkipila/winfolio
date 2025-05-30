<?php

namespace App\Console\Commands;

use App\Models\ProductError;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class LogProductErrorFromNode extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'log:error {productId} {message} {code?} {context?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $message = $this->argument('message');
        $context = $this->argument('context');
        $code = $this->argument('code');
        $productId = $this->argument('productId') ?: NULL;

        Log::error((base64_decode($context)), ['decoded' => base64_decode($context), 'context' => $context]);

        ProductError::create([
            'error' => $message,
            'product_id' => $productId,
            'code' => $code ?? 500,
            'context' => $context ? json_decode(base64_decode($context)) : [],
        ]);
    }
}
