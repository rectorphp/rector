<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RectorPrefix20220501\Symfony\Component\DependencyInjection\Argument;

/**
 * @author Guilhem Niot <guilhem.niot@gmail.com>
 */
final class BoundArgument implements \RectorPrefix20220501\Symfony\Component\DependencyInjection\Argument\ArgumentInterface
{
    public const SERVICE_BINDING = 0;
    public const DEFAULTS_BINDING = 1;
    public const INSTANCEOF_BINDING = 2;
    /**
     * @var int
     */
    private static $sequence = 0;
    /**
     * @var mixed
     */
    private $value;
    /**
     * @var int|null
     */
    private $identifier;
    /**
     * @var bool|null
     */
    private $used;
    /**
     * @var int
     */
    private $type;
    /**
     * @var string|null
     */
    private $file;
    /**
     * @param mixed $value
     */
    public function __construct($value, bool $trackUsage = \true, int $type = 0, string $file = null)
    {
        $this->value = $value;
        if ($trackUsage) {
            $this->identifier = ++self::$sequence;
        } else {
            $this->used = \true;
        }
        $this->type = $type;
        $this->file = $file;
    }
    /**
     * {@inheritdoc}
     */
    public function getValues() : array
    {
        return [$this->value, $this->identifier, $this->used, $this->type, $this->file];
    }
    /**
     * {@inheritdoc}
     */
    public function setValues(array $values)
    {
        if (5 === \count($values)) {
            [$this->value, $this->identifier, $this->used, $this->type, $this->file] = $values;
        } else {
            [$this->value, $this->identifier, $this->used] = $values;
        }
    }
}
