<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RectorPrefix20220418\Symfony\Component\Config\Resource;

use RectorPrefix20220418\Symfony\Component\Config\ResourceCheckerInterface;
/**
 * Resource checker for instances of SelfCheckingResourceInterface.
 *
 * As these resources perform the actual check themselves, we can provide
 * this class as a standard way of validating them.
 *
 * @author Matthias Pigulla <mp@webfactory.de>
 */
class SelfCheckingResourceChecker implements \RectorPrefix20220418\Symfony\Component\Config\ResourceCheckerInterface
{
    // Common shared cache, because this checker can be used in different
    // situations. For example, when using the full stack framework, the router
    // and the container have their own cache. But they may check the very same
    // resources
    /**
     * @var mixed[]
     */
    private static $cache = [];
    public function supports(\RectorPrefix20220418\Symfony\Component\Config\Resource\ResourceInterface $metadata) : bool
    {
        return $metadata instanceof \RectorPrefix20220418\Symfony\Component\Config\Resource\SelfCheckingResourceInterface;
    }
    /**
     * @param SelfCheckingResourceInterface $resource
     */
    public function isFresh(\RectorPrefix20220418\Symfony\Component\Config\Resource\ResourceInterface $resource, int $timestamp) : bool
    {
        $key = "{$resource}:{$timestamp}";
        return self::$cache[$key] ?? (self::$cache[$key] = $resource->isFresh($timestamp));
    }
}
