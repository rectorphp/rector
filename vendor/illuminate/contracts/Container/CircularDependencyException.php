<?php

namespace RectorPrefix202504\Illuminate\Contracts\Container;

use Exception;
use RectorPrefix202504\Psr\Container\ContainerExceptionInterface;
class CircularDependencyException extends Exception implements ContainerExceptionInterface
{
    //
}
