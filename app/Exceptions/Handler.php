<?php

namespace App\Exceptions;

use Throwable;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
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
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $exception
     * @return \Illuminate\Http\Response|\Symfony\Component\HttpFoundation\Response
     */
    public function render($request, Throwable $exception)
    {
        // HttpResponseException already contains a response, just use it.
        if ($exception instanceof HttpResponseException) {
            return $exception->getResponse();
        }

        // Render HttpExceptions as plain text responses. And do it to all
        // exceptions in debug mode.
        if ($exception instanceof HttpException || env('APP_DEBUG')) {
            $statusCode = method_exists($exception, 'getStatusCode') ?
                $exception->getStatusCode() : 500;
            return new Response(
                $exception->getMessage(),
                $statusCode,
                ['Content-Type' => 'text/plain']
            );
        }

        // Convert all other exceptions into internal error.
        return new Response("Internal server error.", 500, ['Content-Type' => 'text/plain']);
    }
}
