<?php

namespace RectorPrefix202501\Illuminate\Container\Attributes;

use Attribute;
use RectorPrefix202501\Illuminate\Contracts\Container\Container;
use RectorPrefix202501\Illuminate\Contracts\Container\ContextualAttribute;
#[Attribute(Attribute::TARGET_PARAMETER)]
class Log implements ContextualAttribute
{
    public ?string $channel = null;
    /**
     * Create a new class instance.
     */
    public function __construct(?string $channel = null)
    {
        $this->channel = $channel;
    }
    /**
     * Resolve the log channel.
     *
     * @param  self  $attribute
     * @param  \Illuminate\Contracts\Container\Container  $container
     * @return \Psr\Log\LoggerInterface
     */
    public static function resolve(self $attribute, Container $container)
    {
        return $container->make('log')->channel($attribute->channel);
    }
}
