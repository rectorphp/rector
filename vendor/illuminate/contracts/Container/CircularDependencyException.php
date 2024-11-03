<?php

namespace RectorPrefix202411\Illuminate\Contracts\Container;

use Exception;
use RectorPrefix202411\Psr\Container\ContainerExceptionInterface;
class CircularDependencyException extends Exception implements ContainerExceptionInterface
{
    //
}
