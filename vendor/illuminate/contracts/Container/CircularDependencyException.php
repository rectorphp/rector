<?php

namespace RectorPrefix202608\Illuminate\Contracts\Container;

use Exception;
use RectorPrefix202608\Psr\Container\ContainerExceptionInterface;
class CircularDependencyException extends Exception implements ContainerExceptionInterface
{
    //
}
