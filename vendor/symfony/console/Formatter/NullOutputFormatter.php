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
final class NullOutputFormatter implements \RectorPrefix20211020\Symfony\Component\Console\Formatter\OutputFormatterInterface
{
    private $style;
    /**
     * {@inheritdoc}
     * @param string|null $message
     */
    public function format($message) : void
    {
        // do nothing
    }
    /**
     * {@inheritdoc}
     * @param string $name
     */
    public function getStyle($name) : \RectorPrefix20211020\Symfony\Component\Console\Formatter\OutputFormatterStyleInterface
    {
        if ($this->style) {
            return $this->style;
        }
        // to comply with the interface we must return a OutputFormatterStyleInterface
        return $this->style = new \RectorPrefix20211020\Symfony\Component\Console\Formatter\NullOutputFormatterStyle();
    }
    /**
     * {@inheritdoc}
     * @param string $name
     */
    public function hasStyle($name) : bool
    {
        return \false;
    }
    /**
     * {@inheritdoc}
     */
    public function isDecorated() : bool
    {
        return \false;
    }
    /**
     * {@inheritdoc}
     * @param bool $decorated
     */
    public function setDecorated($decorated) : void
    {
        // do nothing
    }
    /**
     * {@inheritdoc}
     * @param string $name
     * @param \Symfony\Component\Console\Formatter\OutputFormatterStyleInterface $style
     */
    public function setStyle($name, $style) : void
    {
        // do nothing
    }
}
