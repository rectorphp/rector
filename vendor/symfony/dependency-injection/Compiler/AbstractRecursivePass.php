<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RectorPrefix20220418\Symfony\Component\DependencyInjection\Compiler;

use RectorPrefix20220418\Symfony\Component\DependencyInjection\Argument\ArgumentInterface;
use RectorPrefix20220418\Symfony\Component\DependencyInjection\ChildDefinition;
use RectorPrefix20220418\Symfony\Component\DependencyInjection\ContainerBuilder;
use RectorPrefix20220418\Symfony\Component\DependencyInjection\Definition;
use RectorPrefix20220418\Symfony\Component\DependencyInjection\Exception\LogicException;
use RectorPrefix20220418\Symfony\Component\DependencyInjection\Exception\RuntimeException;
use RectorPrefix20220418\Symfony\Component\DependencyInjection\ExpressionLanguage;
use RectorPrefix20220418\Symfony\Component\DependencyInjection\Reference;
use RectorPrefix20220418\Symfony\Component\ExpressionLanguage\Expression;
/**
 * @author Nicolas Grekas <p@tchwork.com>
 */
abstract class AbstractRecursivePass implements \RectorPrefix20220418\Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface
{
    /**
     * @var ContainerBuilder
     */
    protected $container;
    protected $currentId;
    /**
     * @var bool
     */
    private $processExpressions = \false;
    private $expressionLanguage;
    /**
     * @var bool
     */
    private $inExpression = \false;
    /**
     * {@inheritdoc}
     */
    public function process(\RectorPrefix20220418\Symfony\Component\DependencyInjection\ContainerBuilder $container)
    {
        $this->container = $container;
        try {
            $this->processValue($container->getDefinitions(), \true);
        } finally {
            $this->container = null;
        }
    }
    protected function enableExpressionProcessing()
    {
        $this->processExpressions = \true;
    }
    protected function inExpression(bool $reset = \true) : bool
    {
        $inExpression = $this->inExpression;
        if ($reset) {
            $this->inExpression = \false;
        }
        return $inExpression;
    }
    /**
     * Processes a value found in a definition tree.
     *
     * @return mixed
     * @param mixed $value
     */
    protected function processValue($value, bool $isRoot = \false)
    {
        if (\is_array($value)) {
            foreach ($value as $k => $v) {
                if ($isRoot) {
                    $this->currentId = $k;
                }
                if ($v !== ($processedValue = $this->processValue($v, $isRoot))) {
                    $value[$k] = $processedValue;
                }
            }
        } elseif ($value instanceof \RectorPrefix20220418\Symfony\Component\DependencyInjection\Argument\ArgumentInterface) {
            $value->setValues($this->processValue($value->getValues()));
        } elseif ($value instanceof \RectorPrefix20220418\Symfony\Component\ExpressionLanguage\Expression && $this->processExpressions) {
            $this->getExpressionLanguage()->compile((string) $value, ['this' => 'container']);
        } elseif ($value instanceof \RectorPrefix20220418\Symfony\Component\DependencyInjection\Definition) {
            $value->setArguments($this->processValue($value->getArguments()));
            $value->setProperties($this->processValue($value->getProperties()));
            $value->setMethodCalls($this->processValue($value->getMethodCalls()));
            $changes = $value->getChanges();
            if (isset($changes['factory'])) {
                $value->setFactory($this->processValue($value->getFactory()));
            }
            if (isset($changes['configurator'])) {
                $value->setConfigurator($this->processValue($value->getConfigurator()));
            }
        }
        return $value;
    }
    /**
     * @throws RuntimeException
     */
    protected function getConstructor(\RectorPrefix20220418\Symfony\Component\DependencyInjection\Definition $definition, bool $required) : ?\ReflectionFunctionAbstract
    {
        if ($definition->isSynthetic()) {
            return null;
        }
        if (\is_string($factory = $definition->getFactory())) {
            if (!\function_exists($factory)) {
                throw new \RectorPrefix20220418\Symfony\Component\DependencyInjection\Exception\RuntimeException(\sprintf('Invalid service "%s": function "%s" does not exist.', $this->currentId, $factory));
            }
            $r = new \ReflectionFunction($factory);
            if (\false !== $r->getFileName() && \file_exists($r->getFileName())) {
                $this->container->fileExists($r->getFileName());
            }
            return $r;
        }
        if ($factory) {
            [$class, $method] = $factory;
            if ('__construct' === $method) {
                throw new \RectorPrefix20220418\Symfony\Component\DependencyInjection\Exception\RuntimeException(\sprintf('Invalid service "%s": "__construct()" cannot be used as a factory method.', $this->currentId));
            }
            if ($class instanceof \RectorPrefix20220418\Symfony\Component\DependencyInjection\Reference) {
                $factoryDefinition = $this->container->findDefinition((string) $class);
                while (null === ($class = $factoryDefinition->getClass()) && $factoryDefinition instanceof \RectorPrefix20220418\Symfony\Component\DependencyInjection\ChildDefinition) {
                    $factoryDefinition = $this->container->findDefinition($factoryDefinition->getParent());
                }
            } elseif ($class instanceof \RectorPrefix20220418\Symfony\Component\DependencyInjection\Definition) {
                $class = $class->getClass();
            } elseif (null === $class) {
                $class = $definition->getClass();
            }
            return $this->getReflectionMethod(new \RectorPrefix20220418\Symfony\Component\DependencyInjection\Definition($class), $method);
        }
        while (null === ($class = $definition->getClass()) && $definition instanceof \RectorPrefix20220418\Symfony\Component\DependencyInjection\ChildDefinition) {
            $definition = $this->container->findDefinition($definition->getParent());
        }
        try {
            if (!($r = $this->container->getReflectionClass($class))) {
                if (null === $class) {
                    throw new \RectorPrefix20220418\Symfony\Component\DependencyInjection\Exception\RuntimeException(\sprintf('Invalid service "%s": the class is not set.', $this->currentId));
                }
                throw new \RectorPrefix20220418\Symfony\Component\DependencyInjection\Exception\RuntimeException(\sprintf('Invalid service "%s": class "%s" does not exist.', $this->currentId, $class));
            }
        } catch (\ReflectionException $e) {
            throw new \RectorPrefix20220418\Symfony\Component\DependencyInjection\Exception\RuntimeException(\sprintf('Invalid service "%s": ', $this->currentId) . \lcfirst($e->getMessage()));
        }
        if (!($r = $r->getConstructor())) {
            if ($required) {
                throw new \RectorPrefix20220418\Symfony\Component\DependencyInjection\Exception\RuntimeException(\sprintf('Invalid service "%s": class%s has no constructor.', $this->currentId, \sprintf($class !== $this->currentId ? ' "%s"' : '', $class)));
            }
        } elseif (!$r->isPublic()) {
            throw new \RectorPrefix20220418\Symfony\Component\DependencyInjection\Exception\RuntimeException(\sprintf('Invalid service "%s": ', $this->currentId) . \sprintf($class !== $this->currentId ? 'constructor of class "%s"' : 'its constructor', $class) . ' must be public.');
        }
        return $r;
    }
    /**
     * @throws RuntimeException
     */
    protected function getReflectionMethod(\RectorPrefix20220418\Symfony\Component\DependencyInjection\Definition $definition, string $method) : \ReflectionFunctionAbstract
    {
        if ('__construct' === $method) {
            return $this->getConstructor($definition, \true);
        }
        while (null === ($class = $definition->getClass()) && $definition instanceof \RectorPrefix20220418\Symfony\Component\DependencyInjection\ChildDefinition) {
            $definition = $this->container->findDefinition($definition->getParent());
        }
        if (null === $class) {
            throw new \RectorPrefix20220418\Symfony\Component\DependencyInjection\Exception\RuntimeException(\sprintf('Invalid service "%s": the class is not set.', $this->currentId));
        }
        if (!($r = $this->container->getReflectionClass($class))) {
            throw new \RectorPrefix20220418\Symfony\Component\DependencyInjection\Exception\RuntimeException(\sprintf('Invalid service "%s": class "%s" does not exist.', $this->currentId, $class));
        }
        if (!$r->hasMethod($method)) {
            if ($r->hasMethod('__call') && ($r = $r->getMethod('__call')) && $r->isPublic()) {
                return new \ReflectionMethod(static function (...$arguments) {
                }, '__invoke');
            }
            throw new \RectorPrefix20220418\Symfony\Component\DependencyInjection\Exception\RuntimeException(\sprintf('Invalid service "%s": method "%s()" does not exist.', $this->currentId, $class !== $this->currentId ? $class . '::' . $method : $method));
        }
        $r = $r->getMethod($method);
        if (!$r->isPublic()) {
            throw new \RectorPrefix20220418\Symfony\Component\DependencyInjection\Exception\RuntimeException(\sprintf('Invalid service "%s": method "%s()" must be public.', $this->currentId, $class !== $this->currentId ? $class . '::' . $method : $method));
        }
        return $r;
    }
    private function getExpressionLanguage() : \RectorPrefix20220418\Symfony\Component\DependencyInjection\ExpressionLanguage
    {
        if (!isset($this->expressionLanguage)) {
            if (!\class_exists(\RectorPrefix20220418\Symfony\Component\DependencyInjection\ExpressionLanguage::class)) {
                throw new \RectorPrefix20220418\Symfony\Component\DependencyInjection\Exception\LogicException('Unable to use expressions as the Symfony ExpressionLanguage component is not installed. Try running "composer require symfony/expression-language".');
            }
            $providers = $this->container->getExpressionLanguageProviders();
            $this->expressionLanguage = new \RectorPrefix20220418\Symfony\Component\DependencyInjection\ExpressionLanguage(null, $providers, function (string $arg) : string {
                if ('""' === \substr_replace($arg, '', 1, -1)) {
                    $id = \stripcslashes(\substr($arg, 1, -1));
                    $this->inExpression = \true;
                    $arg = $this->processValue(new \RectorPrefix20220418\Symfony\Component\DependencyInjection\Reference($id));
                    $this->inExpression = \false;
                    if (!$arg instanceof \RectorPrefix20220418\Symfony\Component\DependencyInjection\Reference) {
                        throw new \RectorPrefix20220418\Symfony\Component\DependencyInjection\Exception\RuntimeException(\sprintf('"%s::processValue()" must return a Reference when processing an expression, "%s" returned for service("%s").', static::class, \get_debug_type($arg), $id));
                    }
                    $arg = \sprintf('"%s"', $arg);
                }
                return \sprintf('$this->get(%s)', $arg);
            });
        }
        return $this->expressionLanguage;
    }
}
