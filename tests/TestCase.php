<?php

namespace Tests;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

use App\Exceptions\Handler;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function disableExceptionHandling(){
        $this->app->instance(ExceptionHandler::class, new class extends Handler{
            function __construct(){}
            function report(\Exception $e){}
            function render($request, \Exception $e){ dd($e); throw $e; }
        });
    }
}
