<?php

declare (strict_types=1);
namespace RectorPrefix202208\Symfony\Component\Routing\Annotation;

if (\class_exists('Symfony\\Component\\Routing\\Annotation\\Route')) {
    return;
}
class Route
{
    public function __construct($path, $name = '')
    {
    }
}
