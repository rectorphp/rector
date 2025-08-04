<?php

namespace RectorPrefix202508\Illuminate\Contracts\Container;

use Exception;
use RectorPrefix202508\Psr\Container\ContainerExceptionInterface;
class CircularDependencyException extends Exception implements ContainerExceptionInterface
{
    //
}
