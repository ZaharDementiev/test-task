<?php

declare(strict_types=1);

use App\Exceptions\ServiceException;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

function cpu_count(): int
{
    if ('darwin' === strtolower(PHP_OS)) {
        $count = shell_exec('sysctl -n machdep.cpu.core_count');
    } else {
        $count = shell_exec('nproc');
    }

    return (int)$count > 0 ? (int)$count : 4;
}

function toArr(mixed $data): array
{
    if (is_string($data)) {
        return explode(',', $data);
    }

    if (is_object($data) && method_exists($data, 'toArray')) {
        return $data->toArray();
    }

    return is_array($data) ? $data : [];
}

function toStr(mixed $data, string $default = ''): string
{
    if (is_string($data)) {
        return $data;
    }
    if (is_numeric($data) || $data instanceof Stringable) {
        return (string)$data;
    }

    return $default;
}

function toInt(mixed $data, int $default = 0): int
{
    return is_numeric($data) ? (int)$data : $default;
}

function toFloat(mixed $data, float $default = 0.00): float
{
    return is_numeric($data) ? (float)$data : $default;
}

function arrToStr(array $data): string
{
    return 0 === count($data) ? '' : '["' . implode('","', $data) . '"]';
}

function unmarshal(string $class, array $data): mixed
{
    $reflection = new ReflectionClass($class);

    if (!$reflection->getConstructor()) {
        throw new RuntimeException("Constructor must be specified to unmarshal {$class}");
    }

    if (!$reflection->getConstructor()->isPublic()) {
        throw new RuntimeException("Constructor must be public to unmarshal {$class}");
    }

    $properties = [];
    foreach ($reflection->getConstructor()->getParameters() as $parameter) {
        if ($parameter->allowsNull() && !isset($data[$parameter->getName()])) {
            $properties[] = null;
        } else {
            $properties[] = $data[$parameter->getName()] ??
                throw new RuntimeException("Parameter {$parameter->getName()} must be given to unmarshal {$class}");
        }
    }

    return new $class(...$properties);
}

function marshal(object $object, bool $omitEmpty = false, bool $snake = true): array
{
    $reflection = new ReflectionClass($object);

    if (!$reflection->getConstructor()) {
        throw new RuntimeException('Constructor must be specified to marshal ' . $object::class);
    }

    $data = [];
    foreach ($reflection->getConstructor()->getParameters() as $parameter) {
        $property = $reflection->getProperty($parameter->getName());

        if (!$parameter->isPromoted() || (null === $property->getValue($object) && $omitEmpty)) {
            continue;
        }

        $property->setAccessible(true);
        $data[$parameter->getName()] = $property->getValue($object);
    }

    return $snake ? camelToSnake($data) : $data;
}

function snakeToCamel(array $array): array
{
    foreach ($array as $key => $value) {
        unset($array[$key]);
        $array[lcfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', $key))))] = $value;
    }

    return $array;
}

function camelToSnake(array $array): array
{
    foreach ($array as $key => $value) {
        unset($array[$key]);
        $array[strtolower(toStr(preg_replace('/[A-Z]/', '_$0', lcfirst($key))))] = $value;
    }

    return $array;
}

function responseError($message, int $code = 500): JsonResponse
{
    return response()->json([
        'code' => $code,
        'message' => $message,
    ])->setStatusCode($code);
}

function responseSuccess(mixed $data = null): JsonResponse
{
    return response()->json([
        'code' => ResponseAlias::HTTP_OK,
        'data' => $data,
    ]);
}

function getNow(): string
{
    return Carbon::now()
        ->setTimezone('Europe/Kiev')
        ->toDateTimeString();
}

function bearer(string $authorization): string
{
    return trim(substr($authorization, 7));
}

function yearsDifference(string $date): int
{
    return Carbon::now()
        ->diff(Carbon::now()->subDays((strtotime(Carbon::now()->toDateString()) - strtotime($date)) / (60 * 60 * 24)))
        ->y;
}

function containKey(array $array, string $keyPart): bool
{
    foreach ($array as $key => $value) {
        if (str_contains($key, $keyPart)) {
            return true;
        }
    }

    return false;
}

function getSearchedDates(?string $date): array
{
    $dates = explode(' - ', $date);

    if (count($dates) === 1) {
        $startDate = Carbon::parse($dates[0])->format('Y-m-d H:i:s');
        $endDate = Carbon::now()->endOfDay()->format('Y-m-d H:i:s');
    } else {
        $startDate = Carbon::parse($dates[0])->format('Y-m-d H:i:s');
        $endDate = Carbon::parse($dates[1])->format('Y-m-d H:i:s');
    }

    return [$startDate, $endDate];
}

function getKeyByData(array $data, string|int $search): string|int|null
{
    foreach ($data as $key => $values) {
        if (in_array($search, $values)) {
            $resultKey = $key;
            break;
        }
    }

    return $resultKey ?? null;
}

function splitSearchedDates(?array $dates): array
{
    if ($dates) {
        $startDate = Carbon::make($dates[0])->startOfDay();
        $endDate = Carbon::make($dates[1])->endOfDay();
    } else {
        $startDate = Carbon::now()->subMonth()->startOfDay();
        $endDate = Carbon::now();
    }

    return [$startDate, $endDate];
}

function splitDatesByInterval(Carbon $start, Carbon $end, string $period = 'hour'): array
{
    $timeIntervals = [];

    while ($start < $end) {
        $intervalEnd = clone $start;
        $intervalEnd->modify("+1 $period");

        $timeIntervals[] = [
            'start' => $start->format('Y-m-d H:i:s'),
            'end' => $intervalEnd->format('Y-m-d H:i:s')
        ];

        $start->modify("+1 $period");
    }

    return $timeIntervals;
}

function mergeBoolAtRequest(FormRequest $request, string $key): void
{
    if ($request->input($key) !== null) {
        $request->merge([
            $key => filter_var($request->input($key), FILTER_VALIDATE_BOOLEAN),
        ]);
    }
}

function statusToSnake(string $string): string
{
    $stringWithoutUnderscores = str_replace('_', '', $string);

    return preg_replace('/(?<!^)[A-Z]/', '_$0', strtolower($stringWithoutUnderscores)) . '_status';
}

function throwDeleteError(array $unRemovableItems, string $deletingName): void
{
    if (!empty($unRemovableItems)) {
        $errorMessage = "You cannot delete $deletingName: " . implode(', ', $unRemovableItems);
        throw new ServiceException($errorMessage, ResponseAlias::HTTP_BAD_REQUEST);
    }
}
