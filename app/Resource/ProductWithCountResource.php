<?php

namespace App\Resource;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class ProductWithCountResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var Product $this */

        return [
            'id' => $this->id,
            'name' => $this->name,
            'price' => $this->price,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
            'orders_count' => $this->orders_count,
        ];
    }
}

