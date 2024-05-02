<?php

namespace RectorPrefix202405\Illuminate\Contracts\Container;

use Exception;
use RectorPrefix202405\Psr\Container\ContainerExceptionInterface;
class CircularDependencyException extends Exception implements ContainerExceptionInterface
{
    //
}
