<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class _Set extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'set_num'         => $this->product_num,

            'name'            => $this->name,
            'num_parts'       => $this->num_parts,
            'img_url'         => $this->img_url,
            'year'            => $this->year,
            'review_id'       => $this->review_id,
            'theme_id'        => $this->theme_id,
            'subscription_id' => $this->subscription_id,
            'created_at'      => $this->created_at,
            'updated_at'      => $this->updated_at,

            'review'       => new _Review($this->whenLoaded('review')),
            'theme'        => new _Theme($this->whenLoaded('theme')),
            'subscription' => new _Subscription($this->whenLoaded('subscription')),

            'model' => (new \ReflectionClass($this->resource))->getShortName(),
        ];
    }
}
