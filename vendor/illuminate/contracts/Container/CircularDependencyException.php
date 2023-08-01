<?php

namespace RectorPrefix202308\Illuminate\Contracts\Container;

use Exception;
use RectorPrefix202308\Psr\Container\ContainerExceptionInterface;
class CircularDependencyException extends Exception implements ContainerExceptionInterface
{
    //
}
