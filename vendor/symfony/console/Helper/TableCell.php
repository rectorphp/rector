<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RectorPrefix20211020\Symfony\Component\Console\Helper;

use RectorPrefix20211020\Symfony\Component\Console\Exception\InvalidArgumentException;
/**
 * @author Abdellatif Ait boudad <a.aitboudad@gmail.com>
 */
class TableCell
{
    private $value;
    private $options = ['rowspan' => 1, 'colspan' => 1, 'style' => null];
    public function __construct(string $value = '', array $options = [])
    {
        $this->value = $value;
        // check option names
        if ($diff = \array_diff(\array_keys($options), \array_keys($this->options))) {
            throw new \RectorPrefix20211020\Symfony\Component\Console\Exception\InvalidArgumentException(\sprintf('The TableCell does not support the following options: \'%s\'.', \implode('\', \'', $diff)));
        }
        if (isset($options['style']) && !$options['style'] instanceof \RectorPrefix20211020\Symfony\Component\Console\Helper\TableCellStyle) {
            throw new \RectorPrefix20211020\Symfony\Component\Console\Exception\InvalidArgumentException('The style option must be an instance of "TableCellStyle".');
        }
        $this->options = \array_merge($this->options, $options);
    }
    /**
     * Returns the cell value.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->value;
    }
    /**
     * Gets number of colspan.
     *
     * @return int
     */
    public function getColspan()
    {
        return (int) $this->options['colspan'];
    }
    /**
     * Gets number of rowspan.
     *
     * @return int
     */
    public function getRowspan()
    {
        return (int) $this->options['rowspan'];
    }
    public function getStyle() : ?\RectorPrefix20211020\Symfony\Component\Console\Helper\TableCellStyle
    {
        return $this->options['style'];
    }
}
