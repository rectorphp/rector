<?php

namespace RectorPrefix202512\Illuminate\Container\Attributes;

use Attribute;
use RectorPrefix202512\Illuminate\Contracts\Container\Container;
use RectorPrefix202512\Illuminate\Contracts\Container\ContextualAttribute;
use RectorPrefix202512\Illuminate\Log\Context\Repository;
#[Attribute(Attribute::TARGET_PARAMETER)]
class Context implements ContextualAttribute
{
    public string $key;
    /**
     * @var mixed
     */
    public $default = null;
    public bool $hidden = \false;
    /**
     * Create a new attribute instance.
     * @param mixed $default
     */
    public function __construct(string $key, $default = null, bool $hidden = \false)
    {
        $this->key = $key;
        $this->default = $default;
        $this->hidden = $hidden;
    }
    /**
     * Resolve the context value.
     *
     * @param  self  $attribute
     * @param  \Illuminate\Contracts\Container\Container  $container
     * @return mixed
     */
    public static function resolve(self $attribute, Container $container)
    {
        $repository = $container->make(Repository::class);
        switch ($attribute->hidden) {
            case \true:
                return $repository->getHidden($attribute->key, $attribute->default);
            case \false:
                return $repository->get($attribute->key, $attribute->default);
        }
    }
}
