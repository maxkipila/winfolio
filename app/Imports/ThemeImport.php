<?php

namespace App\Imports;

use App\Models\Theme;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ThemeImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        $parentId = !empty($row['parent_id']) && Theme::find($row['parent_id'])
            ? $row['parent_id']
            : null;

        return Theme::updateOrCreate(
            ['id' => $row['id']],
            [
                'name'      => $row['name'],
                'parent_id' => $parentId
            ]
        );
    }
}
