<?php

namespace App\Providers;


use Illuminate\Contracts\Database\Query\Expression;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    /* public function boot(): void
    {
        Vite::prefetch(concurrency: 3);
    } */

    public function boot(): void
    {
        JsonResource::withoutWrapping();
        Vite::prefetch(concurrency: 3);

        Builder::macro('search', function (array $columns, string $query, $limit = 6, $lang = 'cs', $important = NULL) {

            $orderBy = "CASE ";
            $index = 0;

            $query = str_replace(['\\', '//', '*', '%', '$', '#',  '&', '^'], '', $query);

            $this->where(function ($qq) use ($columns, $query, &$index, &$orderBy, $important) {

                foreach ($columns as $column) {

                    [$a, $b] = $column instanceof Expression ? [$column->getValue(DB::getQueryGrammar()), NULL] : explode(':', $column) + ['', NULL];

                    if ($b) {
                        $qq->orWhereHas($a, fn($qqq) => $qqq->where($b, 'LIKE',  "%" . e($query) . "%"));

                        $builder = new Builder(collect($qq->query->wheres)->last()['query']);
                        $binds = array_filter($builder->getBindings(), fn($bind) => !Str::contains($bind, '%'));

                        [$x, $y] = explode('->', $b) + ['', NULL];

                        $x = $y ? "json_unquote(json_extract(`$x`, '$.\"$y\"'))" : "$x";

                        $when_bindings = fn($i) => match ($i) {
                            0 => [e($query), ...$binds],
                            1 => [e($query) . "%", ...$binds],
                            2 => ["%" . e($query) . "%", ...$binds],
                            3 => ["%" . e($query), ...$binds],
                        };

                        $orderBy .= "WHEN exists ( " . Str::replaceArray('?', array_map(fn($b)  => "'$b'", $builder->getBindings()), $builder->toSql()) . " ) THEN 
                                CASE 
                                    WHEN exists ( " . Str::replaceArray('?', array_map(fn($b)  => "'$b'", $when_bindings(0)), $builder->toSql()) . " ) THEN " . (++$index) . "
                                    WHEN exists ( " . Str::replaceArray('?', array_map(fn($b)  => "'$b'", $when_bindings(1)), $builder->toSql()) . " ) THEN " . (++$index) . "
                                    WHEN exists ( " . Str::replaceArray('?', array_map(fn($b)  => "'$b'", $when_bindings(2)), $builder->toSql()) . " ) THEN " . (++$index) . "
                                    WHEN exists ( " . Str::replaceArray('?', array_map(fn($b)  => "'$b'", $when_bindings(3)), $builder->toSql()) . " ) THEN " . (++$index) . "
                                    ELSE " . (++$index) . "
                                END ";
                    } else {
                        if ($column instanceof Expression)
                            $qq->orWhereRaw("$a LIKE '%" . e($query) . "%'");
                        else
                            $qq->orWhere($a, 'LIKE', "%" . e($query) . "%");

                        if ($important) {
                            $orderBy .= "WHEN $a LIKE '" . e($query) . "' AND " . $important[0] . " = '" . e($important[1]) . "' THEN " . (++$index) . "
                                WHEN $a LIKE '" . e($query) . "%' AND " . $important[0] . " = '" . e($important[1]) . "' THEN " . (++$index) . "
                                WHEN $a LIKE '%" . e($query) . "%' AND " . $important[0] . " = '" . e($important[1]) . "' THEN " . (++$index) . "
                                WHEN $a LIKE '%" . e($query) . "' AND " . $important[0] . " = '" . e($important[1]) . "' THEN " . (++$index) . "                             
                                WHEN " . $important[0] . " = '" . e($important[1]) . "' THEN " . (++$index) . "
                                WHEN $a LIKE '" . e($query) . "' THEN " . (++$index) . "
                                WHEN $a LIKE '" . e($query) . "%' THEN " . (++$index) . "
                                WHEN $a LIKE '%" . e($query) . "%' THEN " . (++$index) . "
                                WHEN $a LIKE '%" . e($query) . "' THEN " . (++$index) . " ";
                        } else {

                            $orderBy .= "WHEN $a LIKE '" . e($query) . "' THEN " . (++$index) . "
                                WHEN $a LIKE '" . e($query) . "%' THEN " . (++$index) . "
                                WHEN $a LIKE '%" . e($query) . "%' THEN " . (++$index) . "
                                WHEN $a LIKE '%" . e($query) . "' THEN " . (++$index) . " ";
                        }
                    }
                }
            });

            $orderBy .=  "ELSE " . (++$index) . " END";

            $this->orderByRaw($orderBy)
                ->limit($limit);

            return $this;
        });


        Builder::macro('orderByRelation', function (array | NULL $_sort, $defaults = ['name', 'asc'], string | NULL $locale = NULL) {

            if ($_sort) {
                // Sorting by request params
                foreach ($_sort as $sort) {
                    if (isset($sort['name']) && isset($sort['order'])) {

                        if (str_contains($sort['name'], '.')) {
                            [$relation, $column] = explode(".", $sort['name']) + [NULL, NULL];

                            try {
                                $Related = $this->getModel()?->{$relation}()?->getRelated();
                                $foreignKey = $this->getModel()?->{$relation}()?->getQualifiedForeignKeyName();
                                $localKey =    $this->getModel()?->{$relation}()?->getQualifiedParentKeyName();

                                $this->orderBy(
                                    $Related::select($column)
                                        ->whereColumn($foreignKey, $localKey)
                                        ->when($locale, fn($q) => $q->whereLocale($locale))
                                        ->orderBy($column, $sort['order'])
                                        ->take(1),
                                    $sort['order']
                                );
                            } catch (\Throwable $th) {
                                $model = $this->getModel()::class;
                                Log::error("There was an attempt to Order By non-existent relationship. \nRelationship: $relation,\nColumn: $column,\nModel: $model", [$th]);
                            }
                        } else
                            $this->orderBy($sort['name'], $sort['order']);
                    }
                }
            } else {
                if (str_contains($defaults[0], '.')) {
                    [$relation, $column] = explode(".", $defaults[0]) + [NULL, NULL];

                    try {
                        $Related = $this->getModel()?->{$relation}()?->getRelated();
                        $foreignKey = $this->getModel()?->{$relation}()?->getQualifiedForeignKeyName();
                        $localKey =    $this->getModel()?->{$relation}()?->getQualifiedParentKeyName();

                        $this->orderBy(
                            $Related::select($column)
                                ->whereColumn($foreignKey, $localKey)
                                ->when($locale, fn($q) => $q->whereLocale($locale))
                                ->orderBy($column, $defaults[1])
                                ->take(1),
                            $defaults[1]
                        );
                    } catch (\Throwable $th) {
                        $model = $this->getModel()::class;
                        Log::error("There was an attempt to Order By non-existent relationship. \nRelationship: $relation,\nColumn: $column,\nModel: $model", [$th]);
                    }
                } else
                    $this->orderBy(...$defaults);
            }

            return $this;
        });
    }
}
