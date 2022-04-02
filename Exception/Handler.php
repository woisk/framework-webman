<?php

namespace Webman\Exception;

use Throwable;
use Webman\Http\Request;
use Webman\Http\Response;

/**
 * Class Handler
 * @package Webman\Exception
 */
class Handler extends ExceptionHandler
{
    public $dontReport = [
        BusinessException::class,
    ];

    public function report(Throwable $exception)
    {
        parent::report($exception);
    }

    public function render(Request $request, Throwable $exception): Response
    {
        return parent::render($request, $exception);
    }

}