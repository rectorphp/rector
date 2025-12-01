<?php

namespace RectorPrefix202512\Illuminate\Contracts\Container;

use Exception;
use RectorPrefix202512\Psr\Container\ContainerExceptionInterface;
class CircularDependencyException extends Exception implements ContainerExceptionInterface
{
    //
}
