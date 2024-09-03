<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\Task\SearchRequest;
use App\Http\Requests\Task\StoreRequest;
use App\Http\Requests\Task\UpdateRequest;
use App\Models\Task;
use App\Repositories\TaskRepository;
use App\Resource\TaskResource;
use Illuminate\Http\JsonResponse;

final readonly class TaskController
{
    public function __construct(
        private TaskRepository $taskRepository
    ) {
    }

    public function index(SearchRequest $request): JsonResponse
    {
        return TaskResource::collection($this->taskRepository->paginate($request->validated()))->response();
    }

    public function store(StoreRequest $request): JsonResponse
    {
        return responseSuccess(new TaskResource($this->taskRepository->create($request->validated())));
    }

    public function update(Task $task, UpdateRequest $request): JsonResponse
    {
        return responseSuccess($task->update($request->validated()));
    }

    public function destroy(Task $task): JsonResponse
    {
        return responseSuccess($this->taskRepository->delete($task->id));
    }

    public function show(Task $task): JsonResponse
    {
        return responseSuccess(new TaskResource($task));
    }
}
