<?php

namespace RectorPrefix202311\Illuminate\Contracts\Container;

use Exception;
use RectorPrefix202311\Psr\Container\ContainerExceptionInterface;
class CircularDependencyException extends Exception implements ContainerExceptionInterface
{
    //
}
