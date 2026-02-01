<?php

namespace RectorPrefix202602\Illuminate\Contracts\Container;

use Exception;
use RectorPrefix202602\Psr\Container\ContainerExceptionInterface;
class CircularDependencyException extends Exception implements ContainerExceptionInterface
{
    //
}
