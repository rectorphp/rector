<?php

namespace RectorPrefix202511\Illuminate\Contracts\Container;

use Exception;
use RectorPrefix202511\Psr\Container\ContainerExceptionInterface;
class CircularDependencyException extends Exception implements ContainerExceptionInterface
{
    //
}
