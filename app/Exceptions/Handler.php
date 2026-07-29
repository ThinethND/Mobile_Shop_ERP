<?php

namespace App\Exceptions;

use App\Traits\ApiResponse;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class Handler extends ExceptionHandler
{

    use ApiResponse;

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    /**
 * Prepare exception for rendering.
 *
 * @param  \Throwable  $e
 * @return \Throwable
 */
public function render($request, Throwable $throwable)
{
    $isApiUrl = $request->is('api/*');
    if($request->acceptsJson() && $isApiUrl) {
        $response = $this->handleException($request, $throwable);
        return $response;
    } else {
        return parent::render($request, $throwable);
    }
}

public function handleException($request, Throwable $exception)
{

    if ($exception instanceof MethodNotAllowedHttpException) {
        return $this->errorResponse('The specified method for the request is invalid', 405);
    }

    if ($exception instanceof NotFoundHttpException) {
        return $this->errorResponse('The specified URL cannot be found', 404);
    }

    if ($exception instanceof HttpException) {
        return $this->errorResponse($exception->getMessage(), $exception->getStatusCode());
    }

    if ($exception instanceof AuthenticationException) {
        return $this->errorResponse('TOKEN_EXPIRED', 401, 'TOKEN_EXPIRED');

}
    // if (config('app.debug')) {
    //     return parent::render($request, $exception);
    // }

    return $this->errorResponse('Unexpected Exception. Try later', 500);

}

protected function unauthenticated($request, AuthenticationException $exception)
{
    $isApiUrl = $request->is('api/*');
    $middlewares = request()->route()->middleware();
     if($isApiUrl) {
        return $this->errorResponse('TOKEN_EXPIRED', 401, 'TOKEN_EXPIRED');
     } else {
        return redirect()->guest(route('login'));
     }
}
}
