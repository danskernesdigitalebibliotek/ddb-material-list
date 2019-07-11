<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use Laravel\Lumen\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\HttpException;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        AuthorizationException::class,
        HttpException::class,
        ModelNotFoundException::class,
        ValidationException::class,
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Exception  $exception
     * @return void
     */
    public function report(Exception $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $exception
     * @return \Illuminate\Http\Response|\Illuminate\Http\JsonResponse
     */
    public function render($request, Exception $exception)
    {
        // HttpResponseException already contains a response, just use it.
        if ($exception instanceof HttpResponseException) {
            return $exception->getResponse();
        }

        // Render HttpExceptions as plain text responses. And do it to all
        // exceptions in debug mode.
        if ($exception instanceof HttpException || env('APP_DEBUG')) {
            return new Response(
                $exception->getMessage(),
                $exception->getStatusCode(),
                ['Content-Type' => 'text/plain']
            );
        }

        // Convert all other exceptions into internal error.
        return new Response("Internal server error.", 500, ['Content-Type' => 'text/plain']);
    }
}
