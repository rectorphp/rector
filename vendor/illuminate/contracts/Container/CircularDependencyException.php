<?php

namespace RectorPrefix202604\Illuminate\Contracts\Container;

use Exception;
use RectorPrefix202604\Psr\Container\ContainerExceptionInterface;
class CircularDependencyException extends Exception implements ContainerExceptionInterface
{
    //
}
