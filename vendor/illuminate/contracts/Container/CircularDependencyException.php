<?php

namespace RectorPrefix202601\Illuminate\Contracts\Container;

use Exception;
use RectorPrefix202601\Psr\Container\ContainerExceptionInterface;
class CircularDependencyException extends Exception implements ContainerExceptionInterface
{
    //
}
