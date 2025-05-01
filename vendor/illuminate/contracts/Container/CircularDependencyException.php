<?php

namespace RectorPrefix202505\Illuminate\Contracts\Container;

use Exception;
use RectorPrefix202505\Psr\Container\ContainerExceptionInterface;
class CircularDependencyException extends Exception implements ContainerExceptionInterface
{
    //
}
