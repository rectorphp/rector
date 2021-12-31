<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RectorPrefix20211231\Symfony\Component\Console\Helper;

use RectorPrefix20211231\Symfony\Component\Console\Exception\InvalidArgumentException;
/**
 * @author Abdellatif Ait boudad <a.aitboudad@gmail.com>
 */
class TableCell
{
    /**
     * @var string
     */
    private $value;
    /**
     * @var mixed[]
     */
    private $options = ['rowspan' => 1, 'colspan' => 1, 'style' => null];
    public function __construct(string $value = '', array $options = [])
    {
        $this->value = $value;
        // check option names
        if ($diff = \array_diff(\array_keys($options), \array_keys($this->options))) {
            throw new \RectorPrefix20211231\Symfony\Component\Console\Exception\InvalidArgumentException(\sprintf('The TableCell does not support the following options: \'%s\'.', \implode('\', \'', $diff)));
        }
        if (isset($options['style']) && !$options['style'] instanceof \RectorPrefix20211231\Symfony\Component\Console\Helper\TableCellStyle) {
            throw new \RectorPrefix20211231\Symfony\Component\Console\Exception\InvalidArgumentException('The style option must be an instance of "TableCellStyle".');
        }
        $this->options = \array_merge($this->options, $options);
    }
    /**
     * Returns the cell value.
     */
    public function __toString() : string
    {
        return $this->value;
    }
    /**
     * Gets number of colspan.
     */
    public function getColspan() : int
    {
        return (int) $this->options['colspan'];
    }
    /**
     * Gets number of rowspan.
     */
    public function getRowspan() : int
    {
        return (int) $this->options['rowspan'];
    }
    public function getStyle() : ?\RectorPrefix20211231\Symfony\Component\Console\Helper\TableCellStyle
    {
        return $this->options['style'];
    }
}
