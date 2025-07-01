<?php

namespace RectorPrefix202507\Illuminate\Contracts\Container;

use Exception;
use RectorPrefix202507\Psr\Container\ContainerExceptionInterface;
class CircularDependencyException extends Exception implements ContainerExceptionInterface
{
    //
}
