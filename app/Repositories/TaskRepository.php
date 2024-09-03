<?php

namespace App\Repositories;

use App\Models\Task;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

final class TaskRepository extends Repository
{
    const PAGINATE_COUNT = 30;

    public function __construct(Task $model)
    {
        parent::__construct($model);
    }

    public function paginate(array $validated): LengthAwarePaginator
    {
        return Task::query()
            ->when(isset($validated['title']), function (Builder $query) use ($validated): void {
                $query->where('tasks.title', 'like', '%' . $validated['title'] . '%');
            })
            ->when(isset($validated['description']), function (Builder $query) use ($validated): void {
                $query->where('tasks.description', 'like', '%' . $validated['description'] . '%');
            })
            ->when(array_key_exists('status', $validated), function (Builder $query) use ($validated): void {
                $query->where('tasks.status', $validated['status']);
            })
            ->orderBy('tasks.id')
            ->paginate(self::PAGINATE_COUNT);
    }
}
