<?php

namespace App\Imports;

use App\Models\Theme;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ThemeImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {

        $parentId = $row['parent_id'] ?? null;
        if ($parentId && !Theme::find($parentId)) {
            $parentId = null;
        }

        return new Theme([
            'id'        => $row['id'],
            'name'      => $row['name'],
            'parent_id' => $parentId,
        ]);
    }
}
