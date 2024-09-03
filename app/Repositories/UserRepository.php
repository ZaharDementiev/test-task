<?php

namespace App\Repositories;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

final class UserRepository extends Repository
{
    public function __construct(User $model)
    {
        parent::__construct($model);
    }

    public function findOrFailByFields(array $fields): User
    {
        return User::query()
            ->where('email', $fields)
            ->firstOrFail();
    }

    public function getPurchasedProducts(int $userId): Collection
    {
        return Product::query()
            ->whereHas('orders', function ($query) use ($userId) {
                $query->where('orders.user_id', $userId)
                    ->where('orders.created_at', '>=', Carbon::now()->subDays(30));
            })
            ->withCount(['orders' => function ($query) use ($userId) {
                $query->where('orders.user_id', $userId)
                    ->where('orders.created_at', '>=', Carbon::now()->subDays(30));
            }])
            ->get();
    }
}
