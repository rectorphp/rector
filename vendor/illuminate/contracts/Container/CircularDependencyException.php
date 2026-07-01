<?php

namespace RectorPrefix202607\Illuminate\Contracts\Container;

use Exception;
use RectorPrefix202607\Psr\Container\ContainerExceptionInterface;
class CircularDependencyException extends Exception implements ContainerExceptionInterface
{
    //
}
