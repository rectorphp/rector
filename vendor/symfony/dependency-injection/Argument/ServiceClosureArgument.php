<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RectorPrefix20220607\Symfony\Component\DependencyInjection\Argument;

use RectorPrefix20220607\Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
/**
 * Represents a service wrapped in a memoizing closure.
 *
 * @author Nicolas Grekas <p@tchwork.com>
 */
class ServiceClosureArgument implements \RectorPrefix20220607\Symfony\Component\DependencyInjection\Argument\ArgumentInterface
{
    /**
     * @var mixed[]
     */
    private $values;
    /**
     * @param mixed $value
     */
    public function __construct($value)
    {
        $this->values = [$value];
    }
    /**
     * {@inheritdoc}
     */
    public function getValues() : array
    {
        return $this->values;
    }
    /**
     * {@inheritdoc}
     */
    public function setValues(array $values)
    {
        if ([0] !== \array_keys($values)) {
            throw new \RectorPrefix20220607\Symfony\Component\DependencyInjection\Exception\InvalidArgumentException('A ServiceClosureArgument must hold one and only one value.');
        }
        $this->values = $values;
    }
}
