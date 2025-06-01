<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RectorPrefix202506\Symfony\Contracts\Service\Attribute;

use RectorPrefix202506\Symfony\Contracts\Service\ServiceMethodsSubscriberTrait;
use RectorPrefix202506\Symfony\Contracts\Service\ServiceSubscriberInterface;
/**
 * For use as the return value for {@see ServiceSubscriberInterface}.
 *
 * @example new SubscribedService('http_client', HttpClientInterface::class, false, new Target('githubApi'))
 *
 * Use with {@see ServiceMethodsSubscriberTrait} to mark a method's return type
 * as a subscribed service.
 *
 * @author Kevin Bond <kevinbond@gmail.com>
 */
#[\Attribute(\Attribute::TARGET_METHOD)]
final class SubscribedService
{
    /**
     * @var string|null
     */
    public ?string $key = null;
    /**
     * @var class-string|null
     */
    public ?string $type = null;
    /**
     * @var bool
     */
    public bool $nullable = \false;
    /** @var object[] */
    public array $attributes;
    /**
     * @param string|null       $key        The key to use for the service
     * @param class-string|null $type       The service class
     * @param bool              $nullable   Whether the service is optional
     * @param object|object[]   $attributes One or more dependency injection attributes to use
     */
    public function __construct(?string $key = null, ?string $type = null, bool $nullable = \false, $attributes = [])
    {
        $this->key = $key;
        $this->type = $type;
        $this->nullable = $nullable;
        $this->attributes = \is_array($attributes) ? $attributes : [$attributes];
    }
}
