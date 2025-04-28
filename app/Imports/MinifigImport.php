<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class MinifigImport implements ToCollection, WithHeadingRow, WithChunkReading
{
    /**
     * Process a collection of rows and perform a bulk upsert.
     */
    public function collection(Collection $rows)
    {
        $data = [];

        foreach ($rows as $row) {
            $data[] = [
                'product_num'  => $row['fig_num'],
                'product_type' => 'minifig',
                'name'         => $row['name'],
                'year'         => null,
                'theme_id'     => null,
                'num_parts'    => isset($row['num_parts']) ? (int)$row['num_parts'] : null,
                'img_url'      => $row['img_url'] ?? null,
                'created_at'   => now(),
                'updated_at'   => now(),
            ];
        }

        DB::table('products')->upsert(
            $data,
            ['product_num'],
            ['name', 'product_type', 'year', 'theme_id', 'num_parts', 'img_url', 'updated_at']
        );
    }

    /*  public function collection(Collection $rows)
    {
        $data = [];

        foreach ($rows as $row) {
            $data[] = [
                'fig_num'   => $row['fig_num'],
                'name'      => $row['name'],
                'num_parts' => isset($row['num_parts']) ? (int)$row['num_parts'] : null,
                'img_url'   => $row['img_url'] ?? null,
            ];
        }

        DB::table('minifigs')->upsert($data, ['fig_num'], ['name', 'num_parts', 'img_url']);
    } */

    /**
     * Define the chunk size for reading the file.
     */
    public function chunkSize(): int
    {
        return 500; // Snižte tuto hodnotu, pokud problém přetrvává
    }
}
