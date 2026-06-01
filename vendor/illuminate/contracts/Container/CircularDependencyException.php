<?php

namespace RectorPrefix202606\Illuminate\Contracts\Container;

use Exception;
use RectorPrefix202606\Psr\Container\ContainerExceptionInterface;
class CircularDependencyException extends Exception implements ContainerExceptionInterface
{
    //
}
