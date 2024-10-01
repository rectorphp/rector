<?php

namespace RectorPrefix202410\Illuminate\Container\Attributes;

use Attribute;
use RectorPrefix202410\Illuminate\Contracts\Container\Container;
use RectorPrefix202410\Illuminate\Contracts\Container\ContextualAttribute;
#[Attribute(Attribute::TARGET_PARAMETER)]
class Auth implements ContextualAttribute
{
    /**
     * @var string|null
     */
    public $guard;
    /**
     * Create a new class instance.
     */
    public function __construct(?string $guard = null)
    {
        $this->guard = $guard;
    }
    /**
     * Resolve the authentication guard.
     *
     * @param  self  $attribute
     * @param  \Illuminate\Contracts\Container\Container  $container
     * @return \Illuminate\Contracts\Auth\Guard|\Illuminate\Contracts\Auth\StatefulGuard
     */
    public static function resolve(self $attribute, Container $container)
    {
        return $container->make('auth')->guard($attribute->guard);
    }
}
