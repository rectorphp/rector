<?php

namespace RectorPrefix202512\Illuminate\Container\Attributes;

use Attribute;
use RectorPrefix202512\Illuminate\Contracts\Container\Container;
use RectorPrefix202512\Illuminate\Contracts\Container\ContextualAttribute;
#[Attribute(Attribute::TARGET_PARAMETER)]
class Give implements ContextualAttribute
{
    /**
     * @var string
     */
    public string $class;
    /**
     * @var array|null
     */
    public array $params = [];
    /**
     * Provide a concrete class implementation for dependency injection.
     *
     * @param  string  $class
     * @param  array|null  $params
     */
    public function __construct(string $class, array $params = [])
    {
        $this->class = $class;
        $this->params = $params;
    }
    /**
     * Resolve the dependency.
     *
     * @param  self  $attribute
     * @param  \Illuminate\Contracts\Container\Container  $container
     * @return mixed
     */
    public static function resolve(self $attribute, Container $container)
    {
        return $container->make($attribute->class, $attribute->params);
    }
}
