<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RectorPrefix20211020\Symfony\Component\DependencyInjection\Compiler;

use RectorPrefix20211020\Symfony\Component\DependencyInjection\Argument\ArgumentInterface;
use RectorPrefix20211020\Symfony\Component\DependencyInjection\ContainerBuilder;
use RectorPrefix20211020\Symfony\Component\DependencyInjection\Definition;
use RectorPrefix20211020\Symfony\Component\DependencyInjection\Reference;
/**
 * Propagate "container.hot_path" tags to referenced services.
 *
 * @author Nicolas Grekas <p@tchwork.com>
 */
class ResolveHotPathPass extends \RectorPrefix20211020\Symfony\Component\DependencyInjection\Compiler\AbstractRecursivePass
{
    private $tagName;
    private $resolvedIds = [];
    public function __construct(string $tagName = 'container.hot_path')
    {
        if (0 < \func_num_args()) {
            trigger_deprecation('symfony/dependency-injection', '5.3', 'Configuring "%s" is deprecated.', __CLASS__);
        }
        $this->tagName = $tagName;
    }
    /**
     * {@inheritdoc}
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     */
    public function process($container)
    {
        try {
            parent::process($container);
            $container->getDefinition('service_container')->clearTag($this->tagName);
        } finally {
            $this->resolvedIds = [];
        }
    }
    /**
     * {@inheritdoc}
     * @param bool $isRoot
     */
    protected function processValue($value, $isRoot = \false)
    {
        if ($value instanceof \RectorPrefix20211020\Symfony\Component\DependencyInjection\Argument\ArgumentInterface) {
            return $value;
        }
        if ($value instanceof \RectorPrefix20211020\Symfony\Component\DependencyInjection\Definition && $isRoot) {
            if ($value->isDeprecated()) {
                return $value->clearTag($this->tagName);
            }
            $this->resolvedIds[$this->currentId] = \true;
            if (!$value->hasTag($this->tagName)) {
                return $value;
            }
        }
        if ($value instanceof \RectorPrefix20211020\Symfony\Component\DependencyInjection\Reference && \RectorPrefix20211020\Symfony\Component\DependencyInjection\ContainerBuilder::IGNORE_ON_UNINITIALIZED_REFERENCE !== $value->getInvalidBehavior() && $this->container->hasDefinition($id = (string) $value)) {
            $definition = $this->container->getDefinition($id);
            if ($definition->isDeprecated() || $definition->hasTag($this->tagName)) {
                return $value;
            }
            $definition->addTag($this->tagName);
            if (isset($this->resolvedIds[$id])) {
                parent::processValue($definition, \false);
            }
            return $value;
        }
        return parent::processValue($value, $isRoot);
    }
}
