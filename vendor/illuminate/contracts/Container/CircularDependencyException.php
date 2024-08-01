<?php

namespace RectorPrefix202408\Illuminate\Contracts\Container;

use Exception;
use RectorPrefix202408\Psr\Container\ContainerExceptionInterface;
class CircularDependencyException extends Exception implements ContainerExceptionInterface
{
    //
}
