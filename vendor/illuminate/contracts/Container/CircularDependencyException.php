<?php

namespace RectorPrefix202407\Illuminate\Contracts\Container;

use Exception;
use RectorPrefix202407\Psr\Container\ContainerExceptionInterface;
class CircularDependencyException extends Exception implements ContainerExceptionInterface
{
    //
}
