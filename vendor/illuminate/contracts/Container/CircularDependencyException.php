<?php

namespace RectorPrefix202412\Illuminate\Contracts\Container;

use Exception;
use RectorPrefix202412\Psr\Container\ContainerExceptionInterface;
class CircularDependencyException extends Exception implements ContainerExceptionInterface
{
    //
}
