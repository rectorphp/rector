<?php

namespace RectorPrefix202312\Illuminate\Contracts\Container;

use Exception;
use RectorPrefix202312\Psr\Container\ContainerExceptionInterface;
class CircularDependencyException extends Exception implements ContainerExceptionInterface
{
    //
}
