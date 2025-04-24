<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RecursiveArrayIterator;
use RecursiveIteratorIterator;

class CreateTranslationsFile extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'translations:generate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    public function translations()
    {

        $flatArray = [];

        foreach (config('app.supported_locales', []) as $key => $locale) {

            $files = File::files(base_path('lang/' . $locale));

            foreach ($files as $key => $file) {
                
                $name = $file->getBasename(".{$file->getExtension()}");
                $arr = Lang::get($name, [], $locale);

                $iterator = new RecursiveIteratorIterator(
                    new RecursiveArrayIterator($arr),
                    RecursiveIteratorIterator::SELF_FIRST
                );
                $path = [];


                foreach ($iterator as $key => $value) {
                    $path[$iterator->getDepth()] = $key;

                    if (!is_array($value)) {
                        $flatArray[$locale . "." . $name . "." . implode('.', array_slice($path, 0, $iterator->getDepth() + 1))] = $value;
                    }
                }
            }
        }



        return $flatArray;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            File::put(base_path("resources/assets/translations.json"), json_encode($this->translations()));
            $this->info("Translations file generated successfully.");
            //code...
        } catch (\Throwable $th) {
            //throw $th;
        }
    }
}
