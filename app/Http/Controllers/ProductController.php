<?php

namespace App\Http\Controllers;

use App\Repositories\ProductRepository;
use App\Resource\ProductWithCountResource;
use Illuminate\Http\JsonResponse;

final readonly class ProductController
{
    public function __construct(
        private ProductRepository $productRepository
    ) {
    }

    public function byUser(): JsonResponse
    {
        return responseSuccess(ProductWithCountResource::collection(
            $this->productRepository->getPurchasedProducts(auth()->id()))
        );
    }

    public function popular(): JsonResponse
    {
        return responseSuccess(
            ProductWithCountResource::collection($this->productRepository->getPopularProducts())
        );
    }
}
