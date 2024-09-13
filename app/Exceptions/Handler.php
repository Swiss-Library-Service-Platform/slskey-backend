<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Response as HttpResponse;
use Throwable;
use Illuminate\Support\Facades\Notification;
use App\Notifications\ExceptionOccurred;

class Handler extends ExceptionHandler
{
    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function render($request, Throwable $e)
    {
        $userLevelCheck = $e instanceof \jeremykenedy\LaravelRoles\App\Exceptions\RoleDeniedException ||
            $e instanceof \jeremykenedy\LaravelRoles\App\Exceptions\PermissionDeniedException ||
            $e instanceof \jeremykenedy\LaravelRoles\App\Exceptions\LevelDeniedException;

        if ($userLevelCheck) {
            if ($request->expectsJson()) {
                return HttpResponse::json([
                    'error' => 403,
                    'message' => 'Unauthorized.',
                ], 403);
            }

            abort(403);
        }

        return parent::render($request, $e);
    }

    public function report(Throwable $exception)
    {
        if ($this->shouldReport($exception)) {
            Notification::route('mail', env('EXCEPTION_MAIL_RECIPIENT'))
                        ->notify(new ExceptionOccurred($exception));
        }

        parent::report($exception);
    }

    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<\Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [
        //
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
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
}
