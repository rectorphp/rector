<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RectorPrefix20211020\Symfony\Component\Config\Builder;

use RectorPrefix20211020\Symfony\Component\Config\Definition\ArrayNode;
use RectorPrefix20211020\Symfony\Component\Config\Definition\BooleanNode;
use RectorPrefix20211020\Symfony\Component\Config\Definition\ConfigurationInterface;
use RectorPrefix20211020\Symfony\Component\Config\Definition\EnumNode;
use RectorPrefix20211020\Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use RectorPrefix20211020\Symfony\Component\Config\Definition\FloatNode;
use RectorPrefix20211020\Symfony\Component\Config\Definition\IntegerNode;
use RectorPrefix20211020\Symfony\Component\Config\Definition\NodeInterface;
use RectorPrefix20211020\Symfony\Component\Config\Definition\PrototypedArrayNode;
use RectorPrefix20211020\Symfony\Component\Config\Definition\ScalarNode;
use RectorPrefix20211020\Symfony\Component\Config\Definition\VariableNode;
use RectorPrefix20211020\Symfony\Component\Config\Loader\ParamConfigurator;
/**
 * Generate ConfigBuilders to help create valid config.
 *
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
class ConfigBuilderGenerator implements \RectorPrefix20211020\Symfony\Component\Config\Builder\ConfigBuilderGeneratorInterface
{
    private $classes;
    private $outputDir;
    public function __construct(string $outputDir)
    {
        $this->outputDir = $outputDir;
    }
    /**
     * @return \Closure that will return the root config class
     * @param \Symfony\Component\Config\Definition\ConfigurationInterface $configuration
     */
    public function build($configuration) : \Closure
    {
        $this->classes = [];
        $rootNode = $configuration->getConfigTreeBuilder()->buildTree();
        $rootClass = new \RectorPrefix20211020\Symfony\Component\Config\Builder\ClassBuilder('RectorPrefix20211020\\Symfony\\Config', $rootNode->getName());
        $path = $this->getFullPath($rootClass);
        if (!\is_file($path)) {
            // Generate the class if the file not exists
            $this->classes[] = $rootClass;
            $this->buildNode($rootNode, $rootClass, $this->getSubNamespace($rootClass));
            $rootClass->addImplements(\RectorPrefix20211020\Symfony\Component\Config\Builder\ConfigBuilderInterface::class);
            $rootClass->addMethod('getExtensionAlias', '
public function NAME(): string
{
    return \'ALIAS\';
}
        ', ['ALIAS' => $rootNode->getPath()]);
            $this->writeClasses();
        }
        $loader = \Closure::fromCallable(function () use($path, $rootClass) {
            require_once $path;
            $className = $rootClass->getFqcn();
            return new $className();
        });
        return $loader;
    }
    private function getFullPath(\RectorPrefix20211020\Symfony\Component\Config\Builder\ClassBuilder $class) : string
    {
        $directory = $this->outputDir . \DIRECTORY_SEPARATOR . $class->getDirectory();
        if (!\is_dir($directory)) {
            @\mkdir($directory, 0777, \true);
        }
        return $directory . \DIRECTORY_SEPARATOR . $class->getFilename();
    }
    private function writeClasses() : void
    {
        foreach ($this->classes as $class) {
            $this->buildConstructor($class);
            $this->buildToArray($class);
            \file_put_contents($this->getFullPath($class), $class->build());
        }
        $this->classes = [];
    }
    private function buildNode(\RectorPrefix20211020\Symfony\Component\Config\Definition\NodeInterface $node, \RectorPrefix20211020\Symfony\Component\Config\Builder\ClassBuilder $class, string $namespace) : void
    {
        if (!$node instanceof \RectorPrefix20211020\Symfony\Component\Config\Definition\ArrayNode) {
            throw new \LogicException('The node was expected to be an ArrayNode. This Configuration includes an edge case not supported yet.');
        }
        foreach ($node->getChildren() as $child) {
            switch (\true) {
                case $child instanceof \RectorPrefix20211020\Symfony\Component\Config\Definition\ScalarNode:
                    $this->handleScalarNode($child, $class);
                    break;
                case $child instanceof \RectorPrefix20211020\Symfony\Component\Config\Definition\PrototypedArrayNode:
                    $this->handlePrototypedArrayNode($child, $class, $namespace);
                    break;
                case $child instanceof \RectorPrefix20211020\Symfony\Component\Config\Definition\VariableNode:
                    $this->handleVariableNode($child, $class);
                    break;
                case $child instanceof \RectorPrefix20211020\Symfony\Component\Config\Definition\ArrayNode:
                    $this->handleArrayNode($child, $class, $namespace);
                    break;
                default:
                    throw new \RuntimeException(\sprintf('Unknown node "%s".', \get_class($child)));
            }
        }
    }
    private function handleArrayNode(\RectorPrefix20211020\Symfony\Component\Config\Definition\ArrayNode $node, \RectorPrefix20211020\Symfony\Component\Config\Builder\ClassBuilder $class, string $namespace) : void
    {
        $childClass = new \RectorPrefix20211020\Symfony\Component\Config\Builder\ClassBuilder($namespace, $node->getName());
        $class->addRequire($childClass);
        $this->classes[] = $childClass;
        $property = $class->addProperty($node->getName(), $childClass->getFqcn());
        $body = '
public function NAME(array $value = []): CLASS
{
    if (null === $this->PROPERTY) {
        $this->PROPERTY = new CLASS($value);
    } elseif ([] !== $value) {
        throw new InvalidConfigurationException(\'The node created by "NAME()" has already been initialized. You cannot pass values the second time you call NAME().\');
    }

    return $this->PROPERTY;
}';
        $class->addUse(\RectorPrefix20211020\Symfony\Component\Config\Definition\Exception\InvalidConfigurationException::class);
        $class->addMethod($node->getName(), $body, ['PROPERTY' => $property->getName(), 'CLASS' => $childClass->getFqcn()]);
        $this->buildNode($node, $childClass, $this->getSubNamespace($childClass));
    }
    private function handleVariableNode(\RectorPrefix20211020\Symfony\Component\Config\Definition\VariableNode $node, \RectorPrefix20211020\Symfony\Component\Config\Builder\ClassBuilder $class) : void
    {
        $comment = $this->getComment($node);
        $property = $class->addProperty($node->getName());
        $class->addUse(\RectorPrefix20211020\Symfony\Component\Config\Loader\ParamConfigurator::class);
        $body = '
/**
COMMENT * @return $this
 */
public function NAME($valueDEFAULT): self
{
    $this->PROPERTY = $value;

    return $this;
}';
        $class->addMethod($node->getName(), $body, ['PROPERTY' => $property->getName(), 'COMMENT' => $comment, 'DEFAULT' => $node->hasDefaultValue() ? ' = ' . \var_export($node->getDefaultValue(), \true) : '']);
    }
    private function handlePrototypedArrayNode(\RectorPrefix20211020\Symfony\Component\Config\Definition\PrototypedArrayNode $node, \RectorPrefix20211020\Symfony\Component\Config\Builder\ClassBuilder $class, string $namespace) : void
    {
        $name = $this->getSingularName($node);
        $prototype = $node->getPrototype();
        $methodName = $name;
        $parameterType = $this->getParameterType($prototype);
        if (null !== $parameterType || $prototype instanceof \RectorPrefix20211020\Symfony\Component\Config\Definition\ScalarNode) {
            $class->addUse(\RectorPrefix20211020\Symfony\Component\Config\Loader\ParamConfigurator::class);
            $property = $class->addProperty($node->getName());
            if (null === ($key = $node->getKeyAttribute())) {
                // This is an array of values; don't use singular name
                $body = '
/**
 * @param ParamConfigurator|list<TYPE|ParamConfigurator> $value
 * @return $this
 */
public function NAME($value): self
{
    $this->PROPERTY = $value;

    return $this;
}';
                $class->addMethod($node->getName(), $body, ['PROPERTY' => $property->getName(), 'TYPE' => '' === $parameterType ? 'mixed' : $parameterType]);
            } else {
                $body = '
/**
 * @param ParamConfigurator|TYPE $value
 * @return $this
 */
public function NAME(string $VAR, $VALUE): self
{
    $this->PROPERTY[$VAR] = $VALUE;

    return $this;
}';
                $class->addMethod($methodName, $body, ['PROPERTY' => $property->getName(), 'TYPE' => '' === $parameterType ? 'mixed' : $parameterType, 'VAR' => '' === $key ? 'key' : $key, 'VALUE' => 'value' === $key ? 'data' : 'value']);
            }
            return;
        }
        $childClass = new \RectorPrefix20211020\Symfony\Component\Config\Builder\ClassBuilder($namespace, $name);
        $class->addRequire($childClass);
        $this->classes[] = $childClass;
        $property = $class->addProperty($node->getName(), $childClass->getFqcn() . '[]');
        if (null === ($key = $node->getKeyAttribute())) {
            $body = '
public function NAME(array $value = []): CLASS
{
    return $this->PROPERTY[] = new CLASS($value);
}';
            $class->addMethod($methodName, $body, ['PROPERTY' => $property->getName(), 'CLASS' => $childClass->getFqcn()]);
        } else {
            $body = '
public function NAME(string $VAR, array $VALUE = []): CLASS
{
    if (!isset($this->PROPERTY[$VAR])) {
        return $this->PROPERTY[$VAR] = new CLASS($value);
    }
    if ([] === $value) {
        return $this->PROPERTY[$VAR];
    }

    throw new InvalidConfigurationException(\'The node created by "NAME()" has already been initialized. You cannot pass values the second time you call NAME().\');
}';
            $class->addUse(\RectorPrefix20211020\Symfony\Component\Config\Definition\Exception\InvalidConfigurationException::class);
            $class->addMethod($methodName, $body, ['PROPERTY' => $property->getName(), 'CLASS' => $childClass->getFqcn(), 'VAR' => '' === $key ? 'key' : $key, 'VALUE' => 'value' === $key ? 'data' : 'value']);
        }
        $this->buildNode($prototype, $childClass, $namespace . '\\' . $childClass->getName());
    }
    private function handleScalarNode(\RectorPrefix20211020\Symfony\Component\Config\Definition\ScalarNode $node, \RectorPrefix20211020\Symfony\Component\Config\Builder\ClassBuilder $class) : void
    {
        $comment = $this->getComment($node);
        $property = $class->addProperty($node->getName());
        $class->addUse(\RectorPrefix20211020\Symfony\Component\Config\Loader\ParamConfigurator::class);
        $body = '
/**
COMMENT * @return $this
 */
public function NAME($value): self
{
    $this->PROPERTY = $value;

    return $this;
}';
        $class->addMethod($node->getName(), $body, ['PROPERTY' => $property->getName(), 'COMMENT' => $comment]);
    }
    private function getParameterType(\RectorPrefix20211020\Symfony\Component\Config\Definition\NodeInterface $node) : ?string
    {
        if ($node instanceof \RectorPrefix20211020\Symfony\Component\Config\Definition\BooleanNode) {
            return 'bool';
        }
        if ($node instanceof \RectorPrefix20211020\Symfony\Component\Config\Definition\IntegerNode) {
            return 'int';
        }
        if ($node instanceof \RectorPrefix20211020\Symfony\Component\Config\Definition\FloatNode) {
            return 'float';
        }
        if ($node instanceof \RectorPrefix20211020\Symfony\Component\Config\Definition\EnumNode) {
            return '';
        }
        if ($node instanceof \RectorPrefix20211020\Symfony\Component\Config\Definition\PrototypedArrayNode && $node->getPrototype() instanceof \RectorPrefix20211020\Symfony\Component\Config\Definition\ScalarNode) {
            // This is just an array of variables
            return 'array';
        }
        if ($node instanceof \RectorPrefix20211020\Symfony\Component\Config\Definition\VariableNode) {
            // mixed
            return '';
        }
        return null;
    }
    private function getComment(\RectorPrefix20211020\Symfony\Component\Config\Definition\VariableNode $node) : string
    {
        $comment = '';
        if ('' !== ($info = (string) $node->getInfo())) {
            $comment .= ' * ' . $info . \PHP_EOL;
        }
        foreach ((array) ($node->getExample() ?? []) as $example) {
            $comment .= ' * @example ' . $example . \PHP_EOL;
        }
        if ('' !== ($default = $node->getDefaultValue())) {
            $comment .= ' * @default ' . (null === $default ? 'null' : \var_export($default, \true)) . \PHP_EOL;
        }
        if ($node instanceof \RectorPrefix20211020\Symfony\Component\Config\Definition\EnumNode) {
            $comment .= \sprintf(' * @param ParamConfigurator|%s $value', \implode('|', \array_map(function ($a) {
                return \var_export($a, \true);
            }, $node->getValues()))) . \PHP_EOL;
        } else {
            $parameterType = $this->getParameterType($node);
            if (null === $parameterType || '' === $parameterType) {
                $parameterType = 'mixed';
            }
            $comment .= ' * @param ParamConfigurator|' . $parameterType . ' $value' . \PHP_EOL;
        }
        if ($node->isDeprecated()) {
            $comment .= ' * @deprecated ' . $node->getDeprecation($node->getName(), $node->getParent()->getName())['message'] . \PHP_EOL;
        }
        return $comment;
    }
    /**
     * Pick a good singular name.
     */
    private function getSingularName(\RectorPrefix20211020\Symfony\Component\Config\Definition\PrototypedArrayNode $node) : string
    {
        $name = $node->getName();
        if ('s' !== \substr($name, -1)) {
            return $name;
        }
        $parent = $node->getParent();
        $mappings = $parent instanceof \RectorPrefix20211020\Symfony\Component\Config\Definition\ArrayNode ? $parent->getXmlRemappings() : [];
        foreach ($mappings as $map) {
            if ($map[1] === $name) {
                $name = $map[0];
                break;
            }
        }
        return $name;
    }
    private function buildToArray(\RectorPrefix20211020\Symfony\Component\Config\Builder\ClassBuilder $class) : void
    {
        $body = '$output = [];';
        foreach ($class->getProperties() as $p) {
            $code = '$this->PROPERTY';
            if (null !== $p->getType()) {
                if ($p->isArray()) {
                    $code = 'array_map(function ($v) { return $v->toArray(); }, $this->PROPERTY)';
                } else {
                    $code = '$this->PROPERTY->toArray()';
                }
            }
            $body .= \strtr('
    if (null !== $this->PROPERTY) {
        $output[\'ORG_NAME\'] = ' . $code . ';
    }', ['PROPERTY' => $p->getName(), 'ORG_NAME' => $p->getOriginalName()]);
        }
        $class->addMethod('toArray', '
public function NAME(): array
{
    ' . $body . '

    return $output;
}
');
    }
    private function buildConstructor(\RectorPrefix20211020\Symfony\Component\Config\Builder\ClassBuilder $class) : void
    {
        $body = '';
        foreach ($class->getProperties() as $p) {
            $code = '$value[\'ORG_NAME\']';
            if (null !== $p->getType()) {
                if ($p->isArray()) {
                    $code = 'array_map(function ($v) { return new ' . $p->getType() . '($v); }, $value[\'ORG_NAME\'])';
                } else {
                    $code = 'new ' . $p->getType() . '($value[\'ORG_NAME\'])';
                }
            }
            $body .= \strtr('
    if (isset($value[\'ORG_NAME\'])) {
        $this->PROPERTY = ' . $code . ';
        unset($value[\'ORG_NAME\']);
    }
', ['PROPERTY' => $p->getName(), 'ORG_NAME' => $p->getOriginalName()]);
        }
        $body .= '
    if ([] !== $value) {
        throw new InvalidConfigurationException(sprintf(\'The following keys are not supported by "%s": \', __CLASS__).implode(\', \', array_keys($value)));
    }';
        $class->addUse(\RectorPrefix20211020\Symfony\Component\Config\Definition\Exception\InvalidConfigurationException::class);
        $class->addMethod('__construct', '
public function __construct(array $value = [])
{
' . $body . '
}
');
    }
    private function getSubNamespace(\RectorPrefix20211020\Symfony\Component\Config\Builder\ClassBuilder $rootClass) : string
    {
        return \sprintf('%s\\%s', $rootClass->getNamespace(), \substr($rootClass->getName(), 0, -6));
    }
}
