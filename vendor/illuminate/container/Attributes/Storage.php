<?php

namespace RectorPrefix202410\Illuminate\Container\Attributes;

use Attribute;
use RectorPrefix202410\Illuminate\Contracts\Container\Container;
use RectorPrefix202410\Illuminate\Contracts\Container\ContextualAttribute;
#[Attribute(Attribute::TARGET_PARAMETER)]
class Storage implements ContextualAttribute
{
    /**
     * @var string|null
     */
    public $disk;
    /**
     * Create a new class instance.
     */
    public function __construct(?string $disk = null)
    {
        $this->disk = $disk;
    }
    /**
     * Resolve the storage disk.
     *
     * @param  self  $attribute
     * @param  \Illuminate\Contracts\Container\Container  $container
     * @return \Illuminate\Contracts\Filesystem\Filesystem
     */
    public static function resolve(self $attribute, Container $container)
    {
        return $container->make('filesystem')->disk($attribute->disk);
    }
}
