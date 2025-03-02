<?php

namespace RectorPrefix202503\Illuminate\Contracts\Container;

use Exception;
use RectorPrefix202503\Psr\Container\ContainerExceptionInterface;
class CircularDependencyException extends Exception implements ContainerExceptionInterface
{
    //
}
