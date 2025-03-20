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
                'fig_num'   => $row['fig_num'],
                'name'      => $row['name'],
                'num_parts' => isset($row['num_parts']) ? (int)$row['num_parts'] : null,
                'img_url'   => $row['img_url'] ?? null,
            ];
        }

        DB::table('minifigs')->upsert($data, ['fig_num'], ['name', 'num_parts', 'img_url']);
    }

    /**
     * Define the chunk size for reading the file.
     */
    public function chunkSize(): int
    {
        return 500; // Snižte tuto hodnotu, pokud problém přetrvává
    }
}
