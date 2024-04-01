<?php

namespace RectorPrefix202404\Illuminate\Contracts\Container;

use Exception;
use RectorPrefix202404\Psr\Container\ContainerExceptionInterface;
class CircularDependencyException extends Exception implements ContainerExceptionInterface
{
    //
}
