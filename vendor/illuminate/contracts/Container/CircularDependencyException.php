<?php

namespace RectorPrefix202510\Illuminate\Contracts\Container;

use Exception;
use RectorPrefix202510\Psr\Container\ContainerExceptionInterface;
class CircularDependencyException extends Exception implements ContainerExceptionInterface
{
    //
}
