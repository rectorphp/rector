<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RectorPrefix20220531\Symfony\Component\Config\Builder;

use RectorPrefix20220531\Symfony\Component\Config\Definition\ArrayNode;
use RectorPrefix20220531\Symfony\Component\Config\Definition\BaseNode;
use RectorPrefix20220531\Symfony\Component\Config\Definition\BooleanNode;
use RectorPrefix20220531\Symfony\Component\Config\Definition\ConfigurationInterface;
use RectorPrefix20220531\Symfony\Component\Config\Definition\EnumNode;
use RectorPrefix20220531\Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use RectorPrefix20220531\Symfony\Component\Config\Definition\FloatNode;
use RectorPrefix20220531\Symfony\Component\Config\Definition\IntegerNode;
use RectorPrefix20220531\Symfony\Component\Config\Definition\NodeInterface;
use RectorPrefix20220531\Symfony\Component\Config\Definition\PrototypedArrayNode;
use RectorPrefix20220531\Symfony\Component\Config\Definition\ScalarNode;
use RectorPrefix20220531\Symfony\Component\Config\Definition\VariableNode;
use RectorPrefix20220531\Symfony\Component\Config\Loader\ParamConfigurator;
/**
 * Generate ConfigBuilders to help create valid config.
 *
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
class ConfigBuilderGenerator implements \RectorPrefix20220531\Symfony\Component\Config\Builder\ConfigBuilderGeneratorInterface
{
    /**
     * @var ClassBuilder[]
     */
    private $classes = [];
    /**
     * @var string
     */
    private $outputDir;
    public function __construct(string $outputDir)
    {
        $this->outputDir = $outputDir;
    }
    /**
     * @return \Closure that will return the root config class
     */
    public function build(\RectorPrefix20220531\Symfony\Component\Config\Definition\ConfigurationInterface $configuration) : \Closure
    {
        $this->classes = [];
        $rootNode = $configuration->getConfigTreeBuilder()->buildTree();
        $rootClass = new \RectorPrefix20220531\Symfony\Component\Config\Builder\ClassBuilder('RectorPrefix20220531\\Symfony\\Config', $rootNode->getName());
        $path = $this->getFullPath($rootClass);
        if (!\is_file($path)) {
            // Generate the class if the file not exists
            $this->classes[] = $rootClass;
            $this->buildNode($rootNode, $rootClass, $this->getSubNamespace($rootClass));
            $rootClass->addImplements(\RectorPrefix20220531\Symfony\Component\Config\Builder\ConfigBuilderInterface::class);
            $rootClass->addMethod('getExtensionAlias', '
public function NAME(): string
{
    return \'ALIAS\';
}', ['ALIAS' => $rootNode->getPath()]);
            $this->writeClasses();
        }
        return function () use($path, $rootClass) {
            require_once $path;
            $className = $rootClass->getFqcn();
            return new $className();
        };
    }
    private function getFullPath(\RectorPrefix20220531\Symfony\Component\Config\Builder\ClassBuilder $class) : string
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
            if ($class->getProperties()) {
                $class->addProperty('_usedProperties', null, '[]');
            }
            $this->buildSetExtraKey($class);
            \file_put_contents($this->getFullPath($class), $class->build());
        }
        $this->classes = [];
    }
    private function buildNode(\RectorPrefix20220531\Symfony\Component\Config\Definition\NodeInterface $node, \RectorPrefix20220531\Symfony\Component\Config\Builder\ClassBuilder $class, string $namespace) : void
    {
        if (!$node instanceof \RectorPrefix20220531\Symfony\Component\Config\Definition\ArrayNode) {
            throw new \LogicException('The node was expected to be an ArrayNode. This Configuration includes an edge case not supported yet.');
        }
        foreach ($node->getChildren() as $child) {
            switch (\true) {
                case $child instanceof \RectorPrefix20220531\Symfony\Component\Config\Definition\ScalarNode:
                    $this->handleScalarNode($child, $class);
                    break;
                case $child instanceof \RectorPrefix20220531\Symfony\Component\Config\Definition\PrototypedArrayNode:
                    $this->handlePrototypedArrayNode($child, $class, $namespace);
                    break;
                case $child instanceof \RectorPrefix20220531\Symfony\Component\Config\Definition\VariableNode:
                    $this->handleVariableNode($child, $class);
                    break;
                case $child instanceof \RectorPrefix20220531\Symfony\Component\Config\Definition\ArrayNode:
                    $this->handleArrayNode($child, $class, $namespace);
                    break;
                default:
                    throw new \RuntimeException(\sprintf('Unknown node "%s".', \get_class($child)));
            }
        }
    }
    private function handleArrayNode(\RectorPrefix20220531\Symfony\Component\Config\Definition\ArrayNode $node, \RectorPrefix20220531\Symfony\Component\Config\Builder\ClassBuilder $class, string $namespace) : void
    {
        $childClass = new \RectorPrefix20220531\Symfony\Component\Config\Builder\ClassBuilder($namespace, $node->getName());
        $childClass->setAllowExtraKeys($node->shouldIgnoreExtraKeys());
        $class->addRequire($childClass);
        $this->classes[] = $childClass;
        $hasNormalizationClosures = $this->hasNormalizationClosures($node);
        $comment = $this->getComment($node);
        if ($hasNormalizationClosures) {
            $comment .= \sprintf(' * @return %s|$this' . "\n ", $childClass->getFqcn());
        }
        if ('' !== $comment) {
            $comment = "/**\n{$comment}*/\n";
        }
        $property = $class->addProperty($node->getName(), $this->getType($childClass->getFqcn(), $hasNormalizationClosures));
        $body = $hasNormalizationClosures ? '
COMMENTpublic function NAME(mixed $value = []): CLASS|static
{
    if (!\\is_array($value)) {
        $this->_usedProperties[\'PROPERTY\'] = true;
        $this->PROPERTY = $value;

        return $this;
    }

    if (!$this->PROPERTY instanceof CLASS) {
        $this->_usedProperties[\'PROPERTY\'] = true;
        $this->PROPERTY = new CLASS($value);
    } elseif (0 < \\func_num_args()) {
        throw new InvalidConfigurationException(\'The node created by "NAME()" has already been initialized. You cannot pass values the second time you call NAME().\');
    }

    return $this->PROPERTY;
}' : '
COMMENTpublic function NAME(array $value = []): CLASS
{
    if (null === $this->PROPERTY) {
        $this->_usedProperties[\'PROPERTY\'] = true;
        $this->PROPERTY = new CLASS($value);
    } elseif (0 < \\func_num_args()) {
        throw new InvalidConfigurationException(\'The node created by "NAME()" has already been initialized. You cannot pass values the second time you call NAME().\');
    }

    return $this->PROPERTY;
}';
        $class->addUse(\RectorPrefix20220531\Symfony\Component\Config\Definition\Exception\InvalidConfigurationException::class);
        $class->addMethod($node->getName(), $body, ['COMMENT' => $comment, 'PROPERTY' => $property->getName(), 'CLASS' => $childClass->getFqcn()]);
        $this->buildNode($node, $childClass, $this->getSubNamespace($childClass));
    }
    private function handleVariableNode(\RectorPrefix20220531\Symfony\Component\Config\Definition\VariableNode $node, \RectorPrefix20220531\Symfony\Component\Config\Builder\ClassBuilder $class) : void
    {
        $comment = $this->getComment($node);
        $property = $class->addProperty($node->getName());
        $class->addUse(\RectorPrefix20220531\Symfony\Component\Config\Loader\ParamConfigurator::class);
        $body = '
/**
COMMENT *
 * @return $this
 */
public function NAME(mixed $valueDEFAULT): static
{
    $this->_usedProperties[\'PROPERTY\'] = true;
    $this->PROPERTY = $value;

    return $this;
}';
        $class->addMethod($node->getName(), $body, ['PROPERTY' => $property->getName(), 'COMMENT' => $comment, 'DEFAULT' => $node->hasDefaultValue() ? ' = ' . \var_export($node->getDefaultValue(), \true) : '']);
    }
    private function handlePrototypedArrayNode(\RectorPrefix20220531\Symfony\Component\Config\Definition\PrototypedArrayNode $node, \RectorPrefix20220531\Symfony\Component\Config\Builder\ClassBuilder $class, string $namespace) : void
    {
        $name = $this->getSingularName($node);
        $prototype = $node->getPrototype();
        $methodName = $name;
        $hasNormalizationClosures = $this->hasNormalizationClosures($node) || $this->hasNormalizationClosures($prototype);
        $parameterType = $this->getParameterType($prototype);
        if (null !== $parameterType || $prototype instanceof \RectorPrefix20220531\Symfony\Component\Config\Definition\ScalarNode) {
            $class->addUse(\RectorPrefix20220531\Symfony\Component\Config\Loader\ParamConfigurator::class);
            $property = $class->addProperty($node->getName());
            if (null === ($key = $node->getKeyAttribute())) {
                // This is an array of values; don't use singular name
                $body = '
/**
 * @param PHPDOC_TYPE $value
 *
 * @return $this
 */
public function NAME(TYPE $value): static
{
    $this->_usedProperties[\'PROPERTY\'] = true;
    $this->PROPERTY = $value;

    return $this;
}';
                $class->addMethod($node->getName(), $body, ['PROPERTY' => $property->getName(), 'TYPE' => $hasNormalizationClosures ? 'mixed' : 'ParamConfigurator|array', 'PHPDOC_TYPE' => $hasNormalizationClosures ? 'mixed' : \sprintf('ParamConfigurator|list<ParamConfigurator|%s>', '' === $parameterType ? 'mixed' : $parameterType)]);
            } else {
                $body = '
/**
 * @return $this
 */
public function NAME(string $VAR, TYPE $VALUE): static
{
    $this->_usedProperties[\'PROPERTY\'] = true;
    $this->PROPERTY[$VAR] = $VALUE;

    return $this;
}';
                $class->addMethod($methodName, $body, ['PROPERTY' => $property->getName(), 'TYPE' => $hasNormalizationClosures || '' === $parameterType ? 'mixed' : 'ParamConfigurator|' . $parameterType, 'VAR' => '' === $key ? 'key' : $key, 'VALUE' => 'value' === $key ? 'data' : 'value']);
            }
            return;
        }
        $childClass = new \RectorPrefix20220531\Symfony\Component\Config\Builder\ClassBuilder($namespace, $name);
        if ($prototype instanceof \RectorPrefix20220531\Symfony\Component\Config\Definition\ArrayNode) {
            $childClass->setAllowExtraKeys($prototype->shouldIgnoreExtraKeys());
        }
        $class->addRequire($childClass);
        $this->classes[] = $childClass;
        $property = $class->addProperty($node->getName(), $this->getType($childClass->getFqcn() . '[]', $hasNormalizationClosures));
        $comment = $this->getComment($node);
        if ($hasNormalizationClosures) {
            $comment .= \sprintf(' * @return %s|$this' . "\n ", $childClass->getFqcn());
        }
        if ('' !== $comment) {
            $comment = "/**\n{$comment}*/\n";
        }
        if (null === ($key = $node->getKeyAttribute())) {
            $body = $hasNormalizationClosures ? '
COMMENTpublic function NAME(mixed $value = []): CLASS|static
{
    $this->_usedProperties[\'PROPERTY\'] = true;
    if (!\\is_array($value)) {
        $this->PROPERTY[] = $value;

        return $this;
    }

    return $this->PROPERTY[] = new CLASS($value);
}' : '
COMMENTpublic function NAME(array $value = []): CLASS
{
    $this->_usedProperties[\'PROPERTY\'] = true;

    return $this->PROPERTY[] = new CLASS($value);
}';
            $class->addMethod($methodName, $body, ['COMMENT' => $comment, 'PROPERTY' => $property->getName(), 'CLASS' => $childClass->getFqcn()]);
        } else {
            $body = $hasNormalizationClosures ? '
COMMENTpublic function NAME(string $VAR, mixed $VALUE = []): CLASS|static
{
    if (!\\is_array($VALUE)) {
        $this->_usedProperties[\'PROPERTY\'] = true;
        $this->PROPERTY[$VAR] = $VALUE;

        return $this;
    }

    if (!isset($this->PROPERTY[$VAR]) || !$this->PROPERTY[$VAR] instanceof CLASS) {
        $this->_usedProperties[\'PROPERTY\'] = true;
        $this->PROPERTY[$VAR] = new CLASS($VALUE);
    } elseif (1 < \\func_num_args()) {
        throw new InvalidConfigurationException(\'The node created by "NAME()" has already been initialized. You cannot pass values the second time you call NAME().\');
    }

    return $this->PROPERTY[$VAR];
}' : '
COMMENTpublic function NAME(string $VAR, array $VALUE = []): CLASS
{
    if (!isset($this->PROPERTY[$VAR])) {
        $this->_usedProperties[\'PROPERTY\'] = true;
        $this->PROPERTY[$VAR] = new CLASS($VALUE);
    } elseif (1 < \\func_num_args()) {
        throw new InvalidConfigurationException(\'The node created by "NAME()" has already been initialized. You cannot pass values the second time you call NAME().\');
    }

    return $this->PROPERTY[$VAR];
}';
            $class->addUse(\RectorPrefix20220531\Symfony\Component\Config\Definition\Exception\InvalidConfigurationException::class);
            $class->addMethod($methodName, $body, ['COMMENT' => $comment, 'PROPERTY' => $property->getName(), 'CLASS' => $childClass->getFqcn(), 'VAR' => '' === $key ? 'key' : $key, 'VALUE' => 'value' === $key ? 'data' : 'value']);
        }
        $this->buildNode($prototype, $childClass, $namespace . '\\' . $childClass->getName());
    }
    private function handleScalarNode(\RectorPrefix20220531\Symfony\Component\Config\Definition\ScalarNode $node, \RectorPrefix20220531\Symfony\Component\Config\Builder\ClassBuilder $class) : void
    {
        $comment = $this->getComment($node);
        $property = $class->addProperty($node->getName());
        $class->addUse(\RectorPrefix20220531\Symfony\Component\Config\Loader\ParamConfigurator::class);
        $body = '
/**
COMMENT * @return $this
 */
public function NAME($value): static
{
    $this->_usedProperties[\'PROPERTY\'] = true;
    $this->PROPERTY = $value;

    return $this;
}';
        $class->addMethod($node->getName(), $body, ['PROPERTY' => $property->getName(), 'COMMENT' => $comment]);
    }
    private function getParameterType(\RectorPrefix20220531\Symfony\Component\Config\Definition\NodeInterface $node) : ?string
    {
        if ($node instanceof \RectorPrefix20220531\Symfony\Component\Config\Definition\BooleanNode) {
            return 'bool';
        }
        if ($node instanceof \RectorPrefix20220531\Symfony\Component\Config\Definition\IntegerNode) {
            return 'int';
        }
        if ($node instanceof \RectorPrefix20220531\Symfony\Component\Config\Definition\FloatNode) {
            return 'float';
        }
        if ($node instanceof \RectorPrefix20220531\Symfony\Component\Config\Definition\EnumNode) {
            return '';
        }
        if ($node instanceof \RectorPrefix20220531\Symfony\Component\Config\Definition\PrototypedArrayNode && $node->getPrototype() instanceof \RectorPrefix20220531\Symfony\Component\Config\Definition\ScalarNode) {
            // This is just an array of variables
            return 'array';
        }
        if ($node instanceof \RectorPrefix20220531\Symfony\Component\Config\Definition\VariableNode) {
            // mixed
            return '';
        }
        return null;
    }
    private function getComment(\RectorPrefix20220531\Symfony\Component\Config\Definition\BaseNode $node) : string
    {
        $comment = '';
        if ('' !== ($info = (string) $node->getInfo())) {
            $comment .= ' * ' . $info . "\n";
        }
        if (!$node instanceof \RectorPrefix20220531\Symfony\Component\Config\Definition\ArrayNode) {
            foreach ((array) ($node->getExample() ?? []) as $example) {
                $comment .= ' * @example ' . $example . "\n";
            }
            if ('' !== ($default = $node->getDefaultValue())) {
                $comment .= ' * @default ' . (null === $default ? 'null' : \var_export($default, \true)) . "\n";
            }
            if ($node instanceof \RectorPrefix20220531\Symfony\Component\Config\Definition\EnumNode) {
                $comment .= \sprintf(' * @param ParamConfigurator|%s $value', \implode('|', \array_map(function ($a) {
                    return \var_export($a, \true);
                }, $node->getValues()))) . "\n";
            } else {
                $parameterType = $this->getParameterType($node);
                if (null === $parameterType || '' === $parameterType) {
                    $parameterType = 'mixed';
                }
                $comment .= ' * @param ParamConfigurator|' . $parameterType . ' $value' . "\n";
            }
        } else {
            foreach ((array) ($node->getExample() ?? []) as $example) {
                $comment .= ' * @example ' . \json_encode($example) . "\n";
            }
            if ($node->hasDefaultValue() && [] != ($default = $node->getDefaultValue())) {
                $comment .= ' * @default ' . \json_encode($default) . "\n";
            }
        }
        if ($node->isDeprecated()) {
            $comment .= ' * @deprecated ' . $node->getDeprecation($node->getName(), $node->getParent()->getName())['message'] . "\n";
        }
        return $comment;
    }
    /**
     * Pick a good singular name.
     */
    private function getSingularName(\RectorPrefix20220531\Symfony\Component\Config\Definition\PrototypedArrayNode $node) : string
    {
        $name = $node->getName();
        if (\substr_compare($name, 's', -\strlen('s')) !== 0) {
            return $name;
        }
        $parent = $node->getParent();
        $mappings = $parent instanceof \RectorPrefix20220531\Symfony\Component\Config\Definition\ArrayNode ? $parent->getXmlRemappings() : [];
        foreach ($mappings as $map) {
            if ($map[1] === $name) {
                $name = $map[0];
                break;
            }
        }
        return $name;
    }
    private function buildToArray(\RectorPrefix20220531\Symfony\Component\Config\Builder\ClassBuilder $class) : void
    {
        $body = '$output = [];';
        foreach ($class->getProperties() as $p) {
            $code = '$this->PROPERTY';
            if (null !== $p->getType()) {
                if ($p->isArray()) {
                    $code = $p->areScalarsAllowed() ? 'array_map(function ($v) { return $v instanceof CLASS ? $v->toArray() : $v; }, $this->PROPERTY)' : 'array_map(function ($v) { return $v->toArray(); }, $this->PROPERTY)';
                } else {
                    $code = $p->areScalarsAllowed() ? '$this->PROPERTY instanceof CLASS ? $this->PROPERTY->toArray() : $this->PROPERTY' : '$this->PROPERTY->toArray()';
                }
            }
            $body .= \strtr('
    if (isset($this->_usedProperties[\'PROPERTY\'])) {
        $output[\'ORG_NAME\'] = ' . $code . ';
    }', ['PROPERTY' => $p->getName(), 'ORG_NAME' => $p->getOriginalName(), 'CLASS' => $p->getType()]);
        }
        $extraKeys = $class->shouldAllowExtraKeys() ? ' + $this->_extraKeys' : '';
        $class->addMethod('toArray', '
public function NAME(): array
{
    ' . $body . '

    return $output' . $extraKeys . ';
}');
    }
    private function buildConstructor(\RectorPrefix20220531\Symfony\Component\Config\Builder\ClassBuilder $class) : void
    {
        $body = '';
        foreach ($class->getProperties() as $p) {
            $code = '$value[\'ORG_NAME\']';
            if (null !== $p->getType()) {
                if ($p->isArray()) {
                    $code = $p->areScalarsAllowed() ? 'array_map(function ($v) { return \\is_array($v) ? new ' . $p->getType() . '($v) : $v; }, $value[\'ORG_NAME\'])' : 'array_map(function ($v) { return new ' . $p->getType() . '($v); }, $value[\'ORG_NAME\'])';
                } else {
                    $code = $p->areScalarsAllowed() ? '\\is_array($value[\'ORG_NAME\']) ? new ' . $p->getType() . '($value[\'ORG_NAME\']) : $value[\'ORG_NAME\']' : 'new ' . $p->getType() . '($value[\'ORG_NAME\'])';
                }
            }
            $body .= \strtr('
    if (array_key_exists(\'ORG_NAME\', $value)) {
        $this->_usedProperties[\'PROPERTY\'] = true;
        $this->PROPERTY = ' . $code . ';
        unset($value[\'ORG_NAME\']);
    }
', ['PROPERTY' => $p->getName(), 'ORG_NAME' => $p->getOriginalName()]);
        }
        if ($class->shouldAllowExtraKeys()) {
            $body .= '
    $this->_extraKeys = $value;
';
        } else {
            $body .= '
    if ([] !== $value) {
        throw new InvalidConfigurationException(sprintf(\'The following keys are not supported by "%s": \', __CLASS__).implode(\', \', array_keys($value)));
    }';
            $class->addUse(\RectorPrefix20220531\Symfony\Component\Config\Definition\Exception\InvalidConfigurationException::class);
        }
        $class->addMethod('__construct', '
public function __construct(array $value = [])
{' . $body . '
}');
    }
    private function buildSetExtraKey(\RectorPrefix20220531\Symfony\Component\Config\Builder\ClassBuilder $class) : void
    {
        if (!$class->shouldAllowExtraKeys()) {
            return;
        }
        $class->addUse(\RectorPrefix20220531\Symfony\Component\Config\Loader\ParamConfigurator::class);
        $class->addProperty('_extraKeys');
        $class->addMethod('set', '
/**
 * @param ParamConfigurator|mixed $value
 *
 * @return $this
 */
public function NAME(string $key, mixed $value): static
{
    $this->_extraKeys[$key] = $value;

    return $this;
}');
    }
    private function getSubNamespace(\RectorPrefix20220531\Symfony\Component\Config\Builder\ClassBuilder $rootClass) : string
    {
        return \sprintf('%s\\%s', $rootClass->getNamespace(), \substr($rootClass->getName(), 0, -6));
    }
    private function hasNormalizationClosures(\RectorPrefix20220531\Symfony\Component\Config\Definition\NodeInterface $node) : bool
    {
        try {
            $r = new \ReflectionProperty($node, 'normalizationClosures');
        } catch (\ReflectionException $exception) {
            return \false;
        }
        $r->setAccessible(\true);
        return [] !== $r->getValue($node);
    }
    private function getType(string $classType, bool $hasNormalizationClosures) : string
    {
        return $classType . ($hasNormalizationClosures ? '|scalar' : '');
    }
}
