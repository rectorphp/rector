<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RectorPrefix202410\Symfony\Component\Console\Completion;

/**
 * Represents a single suggested value.
 *
 * @author Wouter de Jong <wouter@wouterj.nl>
 */
class Suggestion
{
    /**
     * @readonly
     * @var string
     */
    private $value;
    /**
     * @readonly
     * @var string
     */
    private $description = '';
    public function __construct(string $value, string $description = '')
    {
        $this->value = $value;
        $this->description = $description;
    }
    public function getValue() : string
    {
        return $this->value;
    }
    public function getDescription() : string
    {
        return $this->description;
    }
    public function __toString() : string
    {
        return $this->getValue();
    }
}
