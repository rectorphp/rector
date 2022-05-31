<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RectorPrefix20220531\Symfony\Component\DependencyInjection\Compiler;

use RectorPrefix20220531\Symfony\Component\DependencyInjection\ChildDefinition;
use RectorPrefix20220531\Symfony\Component\DependencyInjection\ContainerInterface;
use RectorPrefix20220531\Symfony\Component\DependencyInjection\Definition;
use RectorPrefix20220531\Symfony\Component\DependencyInjection\Exception\ExceptionInterface;
use RectorPrefix20220531\Symfony\Component\DependencyInjection\Exception\RuntimeException;
use RectorPrefix20220531\Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
/**
 * This replaces all ChildDefinition instances with their equivalent fully
 * merged Definition instance.
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 * @author Nicolas Grekas <p@tchwork.com>
 */
class ResolveChildDefinitionsPass extends \RectorPrefix20220531\Symfony\Component\DependencyInjection\Compiler\AbstractRecursivePass
{
    /**
     * @var mixed[]
     */
    private $currentPath;
    /**
     * @param mixed $value
     * @return mixed
     */
    protected function processValue($value, bool $isRoot = \false)
    {
        if (!$value instanceof \RectorPrefix20220531\Symfony\Component\DependencyInjection\Definition) {
            return parent::processValue($value, $isRoot);
        }
        if ($isRoot) {
            // yes, we are specifically fetching the definition from the
            // container to ensure we are not operating on stale data
            $value = $this->container->getDefinition($this->currentId);
        }
        if ($value instanceof \RectorPrefix20220531\Symfony\Component\DependencyInjection\ChildDefinition) {
            $this->currentPath = [];
            $value = $this->resolveDefinition($value);
            if ($isRoot) {
                $this->container->setDefinition($this->currentId, $value);
            }
        }
        return parent::processValue($value, $isRoot);
    }
    /**
     * Resolves the definition.
     *
     * @throws RuntimeException When the definition is invalid
     */
    private function resolveDefinition(\RectorPrefix20220531\Symfony\Component\DependencyInjection\ChildDefinition $definition) : \RectorPrefix20220531\Symfony\Component\DependencyInjection\Definition
    {
        try {
            return $this->doResolveDefinition($definition);
        } catch (\RectorPrefix20220531\Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException $e) {
            throw $e;
        } catch (\RectorPrefix20220531\Symfony\Component\DependencyInjection\Exception\ExceptionInterface $e) {
            $r = new \ReflectionProperty($e, 'message');
            $r->setAccessible(\true);
            $r->setValue($e, \sprintf('Service "%s": %s', $this->currentId, $e->getMessage()));
            throw $e;
        }
    }
    private function doResolveDefinition(\RectorPrefix20220531\Symfony\Component\DependencyInjection\ChildDefinition $definition) : \RectorPrefix20220531\Symfony\Component\DependencyInjection\Definition
    {
        if (!$this->container->has($parent = $definition->getParent())) {
            throw new \RectorPrefix20220531\Symfony\Component\DependencyInjection\Exception\RuntimeException(\sprintf('Parent definition "%s" does not exist.', $parent));
        }
        $searchKey = \array_search($parent, $this->currentPath);
        $this->currentPath[] = $parent;
        if (\false !== $searchKey) {
            throw new \RectorPrefix20220531\Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException($parent, \array_slice($this->currentPath, $searchKey));
        }
        $parentDef = $this->container->findDefinition($parent);
        if ($parentDef instanceof \RectorPrefix20220531\Symfony\Component\DependencyInjection\ChildDefinition) {
            $id = $this->currentId;
            $this->currentId = $parent;
            $parentDef = $this->resolveDefinition($parentDef);
            $this->container->setDefinition($parent, $parentDef);
            $this->currentId = $id;
        }
        $this->container->log($this, \sprintf('Resolving inheritance for "%s" (parent: %s).', $this->currentId, $parent));
        $def = new \RectorPrefix20220531\Symfony\Component\DependencyInjection\Definition();
        // merge in parent definition
        // purposely ignored attributes: abstract, shared, tags, autoconfigured
        $def->setClass($parentDef->getClass());
        $def->setArguments($parentDef->getArguments());
        $def->setMethodCalls($parentDef->getMethodCalls());
        $def->setProperties($parentDef->getProperties());
        if ($parentDef->isDeprecated()) {
            $deprecation = $parentDef->getDeprecation('%service_id%');
            $def->setDeprecated($deprecation['package'], $deprecation['version'], $deprecation['message']);
        }
        $def->setFactory($parentDef->getFactory());
        $def->setConfigurator($parentDef->getConfigurator());
        $def->setFile($parentDef->getFile());
        $def->setPublic($parentDef->isPublic());
        $def->setLazy($parentDef->isLazy());
        $def->setAutowired($parentDef->isAutowired());
        $def->setChanges($parentDef->getChanges());
        $def->setBindings($definition->getBindings() + $parentDef->getBindings());
        $def->setSynthetic($definition->isSynthetic());
        // overwrite with values specified in the decorator
        $changes = $definition->getChanges();
        if (isset($changes['class'])) {
            $def->setClass($definition->getClass());
        }
        if (isset($changes['factory'])) {
            $def->setFactory($definition->getFactory());
        }
        if (isset($changes['configurator'])) {
            $def->setConfigurator($definition->getConfigurator());
        }
        if (isset($changes['file'])) {
            $def->setFile($definition->getFile());
        }
        if (isset($changes['public'])) {
            $def->setPublic($definition->isPublic());
        } else {
            $def->setPublic($parentDef->isPublic());
        }
        if (isset($changes['lazy'])) {
            $def->setLazy($definition->isLazy());
        }
        if (isset($changes['deprecated']) && $definition->isDeprecated()) {
            $deprecation = $definition->getDeprecation('%service_id%');
            $def->setDeprecated($deprecation['package'], $deprecation['version'], $deprecation['message']);
        }
        if (isset($changes['autowired'])) {
            $def->setAutowired($definition->isAutowired());
        }
        if (isset($changes['shared'])) {
            $def->setShared($definition->isShared());
        }
        if (isset($changes['decorated_service'])) {
            $decoratedService = $definition->getDecoratedService();
            if (null === $decoratedService) {
                $def->setDecoratedService($decoratedService);
            } else {
                $def->setDecoratedService($decoratedService[0], $decoratedService[1], $decoratedService[2], $decoratedService[3] ?? \RectorPrefix20220531\Symfony\Component\DependencyInjection\ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE);
            }
        }
        // merge arguments
        foreach ($definition->getArguments() as $k => $v) {
            if (\is_numeric($k)) {
                $def->addArgument($v);
            } elseif (\strncmp($k, 'index_', \strlen('index_')) === 0) {
                $def->replaceArgument((int) \substr($k, \strlen('index_')), $v);
            } else {
                $def->setArgument($k, $v);
            }
        }
        // merge properties
        foreach ($definition->getProperties() as $k => $v) {
            $def->setProperty($k, $v);
        }
        // append method calls
        if ($calls = $definition->getMethodCalls()) {
            $def->setMethodCalls(\array_merge($def->getMethodCalls(), $calls));
        }
        $def->addError($parentDef);
        $def->addError($definition);
        // these attributes are always taken from the child
        $def->setAbstract($definition->isAbstract());
        $def->setTags($definition->getTags());
        // autoconfigure is never taken from parent (on purpose)
        // and it's not legal on an instanceof
        $def->setAutoconfigured($definition->isAutoconfigured());
        if (!$def->hasTag('proxy')) {
            foreach ($parentDef->getTag('proxy') as $v) {
                $def->addTag('proxy', $v);
            }
        }
        return $def;
    }
}
