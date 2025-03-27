<?php

namespace App\Http\Resources;

use App\Traits\isNullable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class _Product extends JsonResource
{
    use isNullable;
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'product_num' => $this->product_num,
            'product_type' => $this->product_type,
            'name'        => $this->name,
            'year'        => $this->year,
            'num_parts'   => $this->num_parts,
            'img_url'     => $this->img_url,
            'theme'       => new _Theme($this->whenLoaded('theme'))
        ];
    }
}
