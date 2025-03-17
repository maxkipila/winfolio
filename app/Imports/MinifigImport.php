<?php

namespace App\Imports;

use App\Models\Minifig;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class MinifigImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        return new Minifig([
            'fig_num'      => $row['fig_num'],
            'name'            => $row['name'],
            'num_parts'       => isset($row['num_parts']) ? (int)$row['num_parts'] : null,
            'img_url'         => $row['img_url'] ?? null,
        ]);
    }
}
