<?php

namespace App\Repositories;

use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

final class ProductRepository extends Repository
{
    const LIMIT = 10;
    const TTL = 60 * 60;
    const CACHE_KEY = 'popular_products_last_month';

    public function __construct(Product $model)
    {
        parent::__construct($model);
    }

    public function getPurchasedProducts(int $userId): Collection
    {
        return Product::query()
            ->whereHas('orders', function (Builder $query) use ($userId): void {
                $query->where('orders.user_id', $userId)
                    ->where('orders.created_at', '>=', Carbon::now()->subMonth());
            })
            ->withCount(['orders' => function (Builder $query) use ($userId): void {
                $query->where('orders.user_id', $userId)
                    ->where('orders.created_at', '>=', Carbon::now()->subMonth());
            }])
            ->get();
    }

    public function getPopularProducts(): Collection
    {
        return Cache::remember(self::CACHE_KEY, self::TTL, function (): Collection {
            return Product::query()
                ->withCount(['orders' => function (Builder $query): void {
                    $query->where('orders.created_at', '>=', Carbon::now()->subMonth());
                }])
                ->orderBy('orders_count', 'desc')
                ->limit(self::LIMIT)
                ->get();
        });
    }
}
