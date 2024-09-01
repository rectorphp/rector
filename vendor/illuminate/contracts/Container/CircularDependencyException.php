<?php

namespace RectorPrefix202409\Illuminate\Contracts\Container;

use Exception;
use RectorPrefix202409\Psr\Container\ContainerExceptionInterface;
class CircularDependencyException extends Exception implements ContainerExceptionInterface
{
    //
}
