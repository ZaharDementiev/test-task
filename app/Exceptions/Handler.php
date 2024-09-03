<?php

declare(strict_types=1);

namespace App\Exceptions;

use App\Models\Crm\Log as EloquentLog;
use ErrorException;
use Exception;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use Throwable;
use Illuminate\Http\Request;

final class Handler extends ExceptionHandler
{
    protected $dontReport = [
        Exception::class,
        ErrorException::class,
    ];

    public static function logError(string $channel, Exception $exception): void
    {
        Log::channel($channel)->error('Description: ' . $exception->getMessage());
        Log::channel($channel)->error($exception->getFile() . ', ' . $exception->getLine());
        Log::channel($channel)->error('------------------');
    }

    public function render($request, Throwable $e): Response
    {
        if ($e instanceof ServiceException) {
            $this->makeLog('stack', $e, $request);

            return responseError($e->getMessage(), $e->getCode());
        } elseif ($e instanceof Exception) {
            $this->storeError($request, $e);
            Log::channel('error_log')->error('Type: ' . $e::class);
            $this->makeLog('error_log', $e, $request);

            return responseError($this->getMessage($e), Response::HTTP_BAD_REQUEST);
        }

        return parent::render($request, $e);
    }

    private function makeLog(string $channel, Exception $e, Request $request): void
    {
        Log::channel($channel)->error('Token: ' . $request->header('Authorization'));
        self::logError($channel, $e);
    }

    private function getMessage(Exception $e): string
    {
        //TODO: uncomment
//        return match (get_class($e)) {
//            ModelNotFoundException::class => 'Data not found',
//            InvalidArgumentException::class => 'Invalid argument or parameters',
//            default => 'Something went wrong',
//        };
        return $e->getMessage();
    }

    private function storeError(Request $request, Exception $e): void
    {
        if ($userId = $request->input('auth_id')) {
            EloquentLog::storeByRoute($e->getMessage(), $userId, $request->route()->getName());
        }
    }
}
