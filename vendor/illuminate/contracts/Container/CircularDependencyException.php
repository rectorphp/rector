<?php

namespace RectorPrefix202502\Illuminate\Contracts\Container;

use Exception;
use RectorPrefix202502\Psr\Container\ContainerExceptionInterface;
class CircularDependencyException extends Exception implements ContainerExceptionInterface
{
    //
}
