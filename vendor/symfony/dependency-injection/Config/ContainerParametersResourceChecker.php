<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RectorPrefix20220501\Symfony\Component\DependencyInjection\Config;

use RectorPrefix20220501\Symfony\Component\Config\Resource\ResourceInterface;
use RectorPrefix20220501\Symfony\Component\Config\ResourceCheckerInterface;
use RectorPrefix20220501\Symfony\Component\DependencyInjection\ContainerInterface;
/**
 * @author Maxime Steinhausser <maxime.steinhausser@gmail.com>
 */
class ContainerParametersResourceChecker implements \RectorPrefix20220501\Symfony\Component\Config\ResourceCheckerInterface
{
    private $container;
    public function __construct(\RectorPrefix20220501\Symfony\Component\DependencyInjection\ContainerInterface $container)
    {
        $this->container = $container;
    }
    /**
     * {@inheritdoc}
     */
    public function supports(\RectorPrefix20220501\Symfony\Component\Config\Resource\ResourceInterface $metadata) : bool
    {
        return $metadata instanceof \RectorPrefix20220501\Symfony\Component\DependencyInjection\Config\ContainerParametersResource;
    }
    /**
     * {@inheritdoc}
     */
    public function isFresh(\RectorPrefix20220501\Symfony\Component\Config\Resource\ResourceInterface $resource, int $timestamp) : bool
    {
        foreach ($resource->getParameters() as $key => $value) {
            if (!$this->container->hasParameter($key) || $this->container->getParameter($key) !== $value) {
                return \false;
            }
        }
        return \true;
    }
}
