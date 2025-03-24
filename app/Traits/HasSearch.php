<?php

namespace App\Traits;

use App\Http\Requests\HerbIndexRequest;
use App\Http\Resources\HerbResource;
use App\Models\Herb;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 *
 */
trait HasSearch
{
    protected function mergeUnique($a1, $a2, $unique)
    {
        return array_values(
            collect(
                array_merge($a1, $a2)
            )
                ->unique($unique)
                ->toArray()
        );
    }

    public function searchByModel($model, $column, $resource, $query, $limit = 6, $language = 'cs')
    {
        return $resource::collection($model::cleverSearch($column, $query, $limit, $language)->get());
    }

    public function searchByModelWL($model, $column, $resource, $query, $limit = 6)
    {
        return $resource::collection($model::cleverSearchWithoutLocale($column, $query, $limit)->get());
    }

    public function searchByModelWLMore($model, $column, $resource, $query, $limit = 6, $wheres = NULL)
    {
        $q = $model::cleverSearchWithoutLocale($column, $query, $limit);

        if ($wheres)
            $q = $wheres($q);

        return $resource::collection($q->get());
    }
}
