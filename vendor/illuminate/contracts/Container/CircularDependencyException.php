<?php

namespace RectorPrefix202501\Illuminate\Contracts\Container;

use Exception;
use RectorPrefix202501\Psr\Container\ContainerExceptionInterface;
class CircularDependencyException extends Exception implements ContainerExceptionInterface
{
    //
}
