<?php

namespace RectorPrefix202411\Illuminate\Container\Attributes;

use Attribute;
use RectorPrefix202411\Illuminate\Contracts\Container\Container;
use RectorPrefix202411\Illuminate\Contracts\Container\ContextualAttribute;
#[Attribute(Attribute::TARGET_PARAMETER)]
class Log implements ContextualAttribute
{
    /**
     * @var string|null
     */
    public $channel;
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
