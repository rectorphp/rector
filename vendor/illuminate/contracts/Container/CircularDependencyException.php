<?php

namespace RectorPrefix202401\Illuminate\Contracts\Container;

use Exception;
use RectorPrefix202401\Psr\Container\ContainerExceptionInterface;
class CircularDependencyException extends Exception implements ContainerExceptionInterface
{
    //
}
