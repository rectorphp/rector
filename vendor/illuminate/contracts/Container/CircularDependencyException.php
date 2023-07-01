<?php

namespace RectorPrefix202307\Illuminate\Contracts\Container;

use Exception;
use RectorPrefix202307\Psr\Container\ContainerExceptionInterface;
class CircularDependencyException extends Exception implements ContainerExceptionInterface
{
    //
}
