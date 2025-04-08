<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Traits\isNullable;

class _Favourite extends JsonResource
{
    use isNullable;

    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'favourite' => $this->favourite?->resource()::init($this->favourite),
            'user_id' => $this->user_id,
            'created_at' => $this->created_at,
        ];
    }
}
