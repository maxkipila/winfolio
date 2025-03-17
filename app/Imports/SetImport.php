<?php

namespace App\Imports;

use App\Models\Set;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class SetImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        return new Set([
            'set_num' => $row['set_num'],
            'name' => $row['name'],
            'year' => $row['year'],
            'theme_id' => $row['theme_id'],
            'num_parts' => $row['num_parts'],
            'img_url' => $row['img_url'] ?? null,
        ]);
    }
}
