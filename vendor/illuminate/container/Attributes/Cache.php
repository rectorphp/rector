<?php

namespace RectorPrefix202410\Illuminate\Container\Attributes;

use Attribute;
use RectorPrefix202410\Illuminate\Contracts\Container\Container;
use RectorPrefix202410\Illuminate\Contracts\Container\ContextualAttribute;
#[Attribute(Attribute::TARGET_PARAMETER)]
class Cache implements ContextualAttribute
{
    /**
     * @var string|null
     */
    public $store;
    /**
     * Create a new class instance.
     */
    public function __construct(?string $store = null)
    {
        $this->store = $store;
    }
    /**
     * Resolve the cache store.
     *
     * @param  self  $attribute
     * @param  \Illuminate\Contracts\Container\Container  $container
     * @return \Illuminate\Contracts\Cache\Repository
     */
    public static function resolve(self $attribute, Container $container)
    {
        return $container->make('cache')->store($attribute->store);
    }
}
