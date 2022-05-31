<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RectorPrefix20220531\Symfony\Component\Config\Definition\Builder;

use RectorPrefix20220531\Symfony\Component\Config\Definition\BaseNode;
use RectorPrefix20220531\Symfony\Component\Config\Definition\Exception\InvalidDefinitionException;
use RectorPrefix20220531\Symfony\Component\Config\Definition\NodeInterface;
/**
 * This class provides a fluent interface for defining a node.
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
abstract class NodeDefinition implements \RectorPrefix20220531\Symfony\Component\Config\Definition\Builder\NodeParentInterface
{
    protected $name;
    protected $normalization;
    protected $validation;
    protected $defaultValue;
    protected $default = \false;
    protected $required = \false;
    protected $deprecation = [];
    protected $merge;
    protected $allowEmptyValue = \true;
    protected $nullEquivalent;
    protected $trueEquivalent = \true;
    protected $falseEquivalent = \false;
    protected $pathSeparator = \RectorPrefix20220531\Symfony\Component\Config\Definition\BaseNode::DEFAULT_PATH_SEPARATOR;
    protected $parent;
    protected $attributes = [];
    public function __construct(?string $name, \RectorPrefix20220531\Symfony\Component\Config\Definition\Builder\NodeParentInterface $parent = null)
    {
        $this->parent = $parent;
        $this->name = $name;
    }
    /**
     * Sets the parent node.
     *
     * @return $this
     */
    public function setParent(\RectorPrefix20220531\Symfony\Component\Config\Definition\Builder\NodeParentInterface $parent)
    {
        $this->parent = $parent;
        return $this;
    }
    /**
     * Sets info message.
     *
     * @return $this
     */
    public function info(string $info)
    {
        return $this->attribute('info', $info);
    }
    /**
     * Sets example configuration.
     *
     * @return $this
     * @param string|mixed[] $example
     */
    public function example($example)
    {
        return $this->attribute('example', $example);
    }
    /**
     * Sets an attribute on the node.
     *
     * @return $this
     * @param mixed $value
     */
    public function attribute(string $key, $value)
    {
        $this->attributes[$key] = $value;
        return $this;
    }
    /**
     * Returns the parent node.
     * @return \Symfony\Component\Config\Definition\Builder\NodeParentInterface|\Symfony\Component\Config\Definition\Builder\NodeBuilder|\Symfony\Component\Config\Definition\Builder\NodeDefinition|\Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition|\Symfony\Component\Config\Definition\Builder\VariableNodeDefinition|null
     */
    public function end()
    {
        return $this->parent;
    }
    /**
     * Creates the node.
     */
    public function getNode(bool $forceRootNode = \false) : \RectorPrefix20220531\Symfony\Component\Config\Definition\NodeInterface
    {
        if ($forceRootNode) {
            $this->parent = null;
        }
        if (null !== $this->normalization) {
            $this->normalization->before = \RectorPrefix20220531\Symfony\Component\Config\Definition\Builder\ExprBuilder::buildExpressions($this->normalization->before);
        }
        if (null !== $this->validation) {
            $this->validation->rules = \RectorPrefix20220531\Symfony\Component\Config\Definition\Builder\ExprBuilder::buildExpressions($this->validation->rules);
        }
        $node = $this->createNode();
        if ($node instanceof \RectorPrefix20220531\Symfony\Component\Config\Definition\BaseNode) {
            $node->setAttributes($this->attributes);
        }
        return $node;
    }
    /**
     * Sets the default value.
     *
     * @return $this
     * @param mixed $value
     */
    public function defaultValue($value)
    {
        $this->default = \true;
        $this->defaultValue = $value;
        return $this;
    }
    /**
     * Sets the node as required.
     *
     * @return $this
     */
    public function isRequired()
    {
        $this->required = \true;
        return $this;
    }
    /**
     * Sets the node as deprecated.
     *
     * @param string $package The name of the composer package that is triggering the deprecation
     * @param string $version The version of the package that introduced the deprecation
     * @param string $message the deprecation message to use
     *
     * You can use %node% and %path% placeholders in your message to display,
     * respectively, the node name and its complete path
     *
     * @return $this
     */
    public function setDeprecated(string $package, string $version, string $message = 'The child node "%node%" at path "%path%" is deprecated.')
    {
        $this->deprecation = ['package' => $package, 'version' => $version, 'message' => $message];
        return $this;
    }
    /**
     * Sets the equivalent value used when the node contains null.
     *
     * @return $this
     * @param mixed $value
     */
    public function treatNullLike($value)
    {
        $this->nullEquivalent = $value;
        return $this;
    }
    /**
     * Sets the equivalent value used when the node contains true.
     *
     * @return $this
     * @param mixed $value
     */
    public function treatTrueLike($value)
    {
        $this->trueEquivalent = $value;
        return $this;
    }
    /**
     * Sets the equivalent value used when the node contains false.
     *
     * @return $this
     * @param mixed $value
     */
    public function treatFalseLike($value)
    {
        $this->falseEquivalent = $value;
        return $this;
    }
    /**
     * Sets null as the default value.
     *
     * @return $this
     */
    public function defaultNull()
    {
        return $this->defaultValue(null);
    }
    /**
     * Sets true as the default value.
     *
     * @return $this
     */
    public function defaultTrue()
    {
        return $this->defaultValue(\true);
    }
    /**
     * Sets false as the default value.
     *
     * @return $this
     */
    public function defaultFalse()
    {
        return $this->defaultValue(\false);
    }
    /**
     * Sets an expression to run before the normalization.
     */
    public function beforeNormalization() : \RectorPrefix20220531\Symfony\Component\Config\Definition\Builder\ExprBuilder
    {
        return $this->normalization()->before();
    }
    /**
     * Denies the node value being empty.
     *
     * @return $this
     */
    public function cannotBeEmpty()
    {
        $this->allowEmptyValue = \false;
        return $this;
    }
    /**
     * Sets an expression to run for the validation.
     *
     * The expression receives the value of the node and must return it. It can
     * modify it.
     * An exception should be thrown when the node is not valid.
     */
    public function validate() : \RectorPrefix20220531\Symfony\Component\Config\Definition\Builder\ExprBuilder
    {
        return $this->validation()->rule();
    }
    /**
     * Sets whether the node can be overwritten.
     *
     * @return $this
     */
    public function cannotBeOverwritten(bool $deny = \true)
    {
        $this->merge()->denyOverwrite($deny);
        return $this;
    }
    /**
     * Gets the builder for validation rules.
     */
    protected function validation() : \RectorPrefix20220531\Symfony\Component\Config\Definition\Builder\ValidationBuilder
    {
        if (null === $this->validation) {
            $this->validation = new \RectorPrefix20220531\Symfony\Component\Config\Definition\Builder\ValidationBuilder($this);
        }
        return $this->validation;
    }
    /**
     * Gets the builder for merging rules.
     */
    protected function merge() : \RectorPrefix20220531\Symfony\Component\Config\Definition\Builder\MergeBuilder
    {
        if (null === $this->merge) {
            $this->merge = new \RectorPrefix20220531\Symfony\Component\Config\Definition\Builder\MergeBuilder($this);
        }
        return $this->merge;
    }
    /**
     * Gets the builder for normalization rules.
     */
    protected function normalization() : \RectorPrefix20220531\Symfony\Component\Config\Definition\Builder\NormalizationBuilder
    {
        if (null === $this->normalization) {
            $this->normalization = new \RectorPrefix20220531\Symfony\Component\Config\Definition\Builder\NormalizationBuilder($this);
        }
        return $this->normalization;
    }
    /**
     * Instantiate and configure the node according to this definition.
     *
     * @throws InvalidDefinitionException When the definition is invalid
     */
    protected abstract function createNode() : \RectorPrefix20220531\Symfony\Component\Config\Definition\NodeInterface;
    /**
     * Set PathSeparator to use.
     *
     * @return $this
     */
    public function setPathSeparator(string $separator)
    {
        if ($this instanceof \RectorPrefix20220531\Symfony\Component\Config\Definition\Builder\ParentNodeDefinitionInterface) {
            foreach ($this->getChildNodeDefinitions() as $child) {
                $child->setPathSeparator($separator);
            }
        }
        $this->pathSeparator = $separator;
        return $this;
    }
}
