<?php


namespace App\Imports;

use App\Models\Set;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class SetImport implements ToModel, WithHeadingRow, WithChunkReading
{
    public function model(array $row)
    {
        // $themeId = \App\Models\Theme::find($row['theme_id']) ? $row['theme_id'] : null; //existuje ?

        return Set::updateOrCreate(
            ['set_num' => $row['set_num']],
            [
                'name'      => $row['name'],
                'year'      => $row['year'],
                'theme_id'  => $row['theme_id'],
                'num_parts' => $row['num_parts'],
                'img_url'   => $row['img_url'] ?? null,
            ]
        );
    }

    /**
     * Define the chunk size for reading the file.
     */
    public function chunkSize(): int
    {
        return 500;
    }
}
