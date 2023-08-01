<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RectorPrefix202308\Symfony\Component\Config\Definition;

use RectorPrefix202308\Symfony\Component\Config\Definition\Exception\ForbiddenOverwriteException;
use RectorPrefix202308\Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use RectorPrefix202308\Symfony\Component\Config\Definition\Exception\InvalidTypeException;
/**
 * Common Interface among all nodes.
 *
 * In most cases, it is better to inherit from BaseNode instead of implementing
 * this interface yourself.
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
interface NodeInterface
{
    /**
     * Returns the name of the node.
     */
    public function getName() : string;
    /**
     * Returns the path of the node.
     */
    public function getPath() : string;
    /**
     * Returns true when the node is required.
     */
    public function isRequired() : bool;
    /**
     * Returns true when the node has a default value.
     */
    public function hasDefaultValue() : bool;
    /**
     * Returns the default value of the node.
     *
     * @throws \RuntimeException if the node has no default value
     * @return mixed
     */
    public function getDefaultValue();
    /**
     * Normalizes a value.
     *
     * @throws InvalidTypeException if the value type is invalid
     * @param mixed $value
     * @return mixed
     */
    public function normalize($value);
    /**
     * Merges two values together.
     *
     * @throws ForbiddenOverwriteException if the configuration path cannot be overwritten
     * @throws InvalidTypeException        if the value type is invalid
     * @param mixed $leftSide
     * @param mixed $rightSide
     * @return mixed
     */
    public function merge($leftSide, $rightSide);
    /**
     * Finalizes a value.
     *
     * @throws InvalidTypeException          if the value type is invalid
     * @throws InvalidConfigurationException if the value is invalid configuration
     * @param mixed $value
     * @return mixed
     */
    public function finalize($value);
}
