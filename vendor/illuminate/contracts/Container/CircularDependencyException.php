<?php

namespace RectorPrefix202410\Illuminate\Contracts\Container;

use Exception;
use RectorPrefix202410\Psr\Container\ContainerExceptionInterface;
class CircularDependencyException extends Exception implements ContainerExceptionInterface
{
    //
}
