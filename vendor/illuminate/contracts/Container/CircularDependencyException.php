<?php

namespace RectorPrefix202506\Illuminate\Contracts\Container;

use Exception;
use RectorPrefix202506\Psr\Container\ContainerExceptionInterface;
class CircularDependencyException extends Exception implements ContainerExceptionInterface
{
    //
}
