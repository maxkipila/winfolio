<?php

namespace App\Http\Resources;

use App\Traits\isNullable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class _ProductError extends JsonResource
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
            'id' => $this->id,
            'error' => $this->error,
            'context' => $this->context,
            'code' => $this->code,
            'product' => _Product::init($this->product),
            'created_at' => $this->created_at->format('d. m. Y H:i:s'),
        ];
    }
}
