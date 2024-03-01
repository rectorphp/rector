<?php

namespace RectorPrefix202403\Illuminate\Contracts\Container;

use Exception;
use RectorPrefix202403\Psr\Container\ContainerExceptionInterface;
class CircularDependencyException extends Exception implements ContainerExceptionInterface
{
    //
}
