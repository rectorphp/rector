<?php

namespace RectorPrefix202402\Illuminate\Contracts\Container;

use Exception;
use RectorPrefix202402\Psr\Container\ContainerExceptionInterface;
class CircularDependencyException extends Exception implements ContainerExceptionInterface
{
    //
}
