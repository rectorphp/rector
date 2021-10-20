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
use RectorPrefix20211020\Symfony\Component\DependencyInjection\Argument\ServiceClosureArgument;
use RectorPrefix20211020\Symfony\Component\DependencyInjection\ContainerBuilder;
use RectorPrefix20211020\Symfony\Component\DependencyInjection\ContainerInterface;
use RectorPrefix20211020\Symfony\Component\DependencyInjection\Definition;
use RectorPrefix20211020\Symfony\Component\DependencyInjection\Exception\RuntimeException;
use RectorPrefix20211020\Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use RectorPrefix20211020\Symfony\Component\DependencyInjection\Reference;
use RectorPrefix20211020\Symfony\Component\DependencyInjection\TypedReference;
/**
 * Emulates the invalid behavior if the reference is not found within the
 * container.
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
class ResolveInvalidReferencesPass implements \RectorPrefix20211020\Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface
{
    private $container;
    private $signalingException;
    private $currentId;
    /**
     * Process the ContainerBuilder to resolve invalid references.
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     */
    public function process($container)
    {
        $this->container = $container;
        $this->signalingException = new \RectorPrefix20211020\Symfony\Component\DependencyInjection\Exception\RuntimeException('Invalid reference.');
        try {
            foreach ($container->getDefinitions() as $this->currentId => $definition) {
                $this->processValue($definition);
            }
        } finally {
            $this->container = $this->signalingException = null;
        }
    }
    /**
     * Processes arguments to determine invalid references.
     *
     * @return mixed
     *
     * @throws RuntimeException When an invalid reference is found
     */
    private function processValue($value, int $rootLevel = 0, int $level = 0)
    {
        if ($value instanceof \RectorPrefix20211020\Symfony\Component\DependencyInjection\Argument\ServiceClosureArgument) {
            $value->setValues($this->processValue($value->getValues(), 1, 1));
        } elseif ($value instanceof \RectorPrefix20211020\Symfony\Component\DependencyInjection\Argument\ArgumentInterface) {
            $value->setValues($this->processValue($value->getValues(), $rootLevel, 1 + $level));
        } elseif ($value instanceof \RectorPrefix20211020\Symfony\Component\DependencyInjection\Definition) {
            if ($value->isSynthetic() || $value->isAbstract()) {
                return $value;
            }
            $value->setArguments($this->processValue($value->getArguments(), 0));
            $value->setProperties($this->processValue($value->getProperties(), 1));
            $value->setMethodCalls($this->processValue($value->getMethodCalls(), 2));
        } elseif (\is_array($value)) {
            $i = 0;
            foreach ($value as $k => $v) {
                try {
                    if (\false !== $i && $k !== $i++) {
                        $i = \false;
                    }
                    if ($v !== ($processedValue = $this->processValue($v, $rootLevel, 1 + $level))) {
                        $value[$k] = $processedValue;
                    }
                } catch (\RectorPrefix20211020\Symfony\Component\DependencyInjection\Exception\RuntimeException $e) {
                    if ($rootLevel < $level || $rootLevel && !$level) {
                        unset($value[$k]);
                    } elseif ($rootLevel) {
                        throw $e;
                    } else {
                        $value[$k] = null;
                    }
                }
            }
            // Ensure numerically indexed arguments have sequential numeric keys.
            if (\false !== $i) {
                $value = \array_values($value);
            }
        } elseif ($value instanceof \RectorPrefix20211020\Symfony\Component\DependencyInjection\Reference) {
            if ($this->container->has($id = (string) $value)) {
                return $value;
            }
            $currentDefinition = $this->container->getDefinition($this->currentId);
            // resolve decorated service behavior depending on decorator service
            if ($currentDefinition->innerServiceId === $id && \RectorPrefix20211020\Symfony\Component\DependencyInjection\ContainerInterface::NULL_ON_INVALID_REFERENCE === $currentDefinition->decorationOnInvalid) {
                return null;
            }
            $invalidBehavior = $value->getInvalidBehavior();
            if (\RectorPrefix20211020\Symfony\Component\DependencyInjection\ContainerInterface::RUNTIME_EXCEPTION_ON_INVALID_REFERENCE === $invalidBehavior && $value instanceof \RectorPrefix20211020\Symfony\Component\DependencyInjection\TypedReference && !$this->container->has($id)) {
                $e = new \RectorPrefix20211020\Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException($id, $this->currentId);
                // since the error message varies by $id and $this->currentId, so should the id of the dummy errored definition
                $this->container->register($id = \sprintf('.errored.%s.%s', $this->currentId, $id), $value->getType())->addError($e->getMessage());
                return new \RectorPrefix20211020\Symfony\Component\DependencyInjection\TypedReference($id, $value->getType(), $value->getInvalidBehavior());
            }
            // resolve invalid behavior
            if (\RectorPrefix20211020\Symfony\Component\DependencyInjection\ContainerInterface::NULL_ON_INVALID_REFERENCE === $invalidBehavior) {
                $value = null;
            } elseif (\RectorPrefix20211020\Symfony\Component\DependencyInjection\ContainerInterface::IGNORE_ON_INVALID_REFERENCE === $invalidBehavior) {
                if (0 < $level || $rootLevel) {
                    throw $this->signalingException;
                }
                $value = null;
            }
        }
        return $value;
    }
}
