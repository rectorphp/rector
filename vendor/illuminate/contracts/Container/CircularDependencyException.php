<?php

namespace RectorPrefix202309\Illuminate\Contracts\Container;

use Exception;
use RectorPrefix202309\Psr\Container\ContainerExceptionInterface;
class CircularDependencyException extends Exception implements ContainerExceptionInterface
{
    //
}
