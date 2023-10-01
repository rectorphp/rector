<?php

namespace RectorPrefix202310\Illuminate\Contracts\Container;

use Exception;
use RectorPrefix202310\Psr\Container\ContainerExceptionInterface;
class CircularDependencyException extends Exception implements ContainerExceptionInterface
{
    //
}
