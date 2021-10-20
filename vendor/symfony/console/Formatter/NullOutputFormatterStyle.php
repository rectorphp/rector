<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RectorPrefix20211020\Symfony\Component\Console\Formatter;

/**
 * @author Tien Xuan Vo <tien.xuan.vo@gmail.com>
 */
final class NullOutputFormatterStyle implements \RectorPrefix20211020\Symfony\Component\Console\Formatter\OutputFormatterStyleInterface
{
    /**
     * {@inheritdoc}
     * @param string $text
     */
    public function apply($text) : string
    {
        return $text;
    }
    /**
     * {@inheritdoc}
     * @param string|null $color
     */
    public function setBackground($color = null) : void
    {
        // do nothing
    }
    /**
     * {@inheritdoc}
     * @param string|null $color
     */
    public function setForeground($color = null) : void
    {
        // do nothing
    }
    /**
     * {@inheritdoc}
     * @param string $option
     */
    public function setOption($option) : void
    {
        // do nothing
    }
    /**
     * {@inheritdoc}
     * @param mixed[] $options
     */
    public function setOptions($options) : void
    {
        // do nothing
    }
    /**
     * {@inheritdoc}
     * @param string $option
     */
    public function unsetOption($option) : void
    {
        // do nothing
    }
}
