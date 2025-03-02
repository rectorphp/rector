<?php

namespace RectorPrefix202503\Illuminate\Container\Attributes;

use Attribute;
use RectorPrefix202503\Illuminate\Contracts\Container\Container;
use RectorPrefix202503\Illuminate\Contracts\Container\ContextualAttribute;
#[Attribute(Attribute::TARGET_PARAMETER)]
class RouteParameter implements ContextualAttribute
{
    public string $parameter;
    /**
     * Create a new class instance.
     */
    public function __construct(string $parameter)
    {
        $this->parameter = $parameter;
    }
    /**
     * Resolve the route parameter.
     *
     * @param  self  $attribute
     * @param  \Illuminate\Contracts\Container\Container  $container
     * @return mixed
     */
    public static function resolve(self $attribute, Container $container)
    {
        return $container->make('request')->route($attribute->parameter);
    }
}
