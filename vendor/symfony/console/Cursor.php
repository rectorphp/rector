<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RectorPrefix20211020\Symfony\Component\Console;

use RectorPrefix20211020\Symfony\Component\Console\Output\OutputInterface;
/**
 * @author Pierre du Plessis <pdples@gmail.com>
 */
final class Cursor
{
    private $output;
    private $input;
    public function __construct(\RectorPrefix20211020\Symfony\Component\Console\Output\OutputInterface $output, $input = null)
    {
        $this->output = $output;
        $this->input = $input ?? (\defined('STDIN') ? \STDIN : \fopen('php://input', 'r+'));
    }
    public function moveUp(int $lines = 1) : self
    {
        $this->output->write(\sprintf("\33[%dA", $lines));
        return $this;
    }
    public function moveDown(int $lines = 1) : self
    {
        $this->output->write(\sprintf("\33[%dB", $lines));
        return $this;
    }
    public function moveRight(int $columns = 1) : self
    {
        $this->output->write(\sprintf("\33[%dC", $columns));
        return $this;
    }
    public function moveLeft(int $columns = 1) : self
    {
        $this->output->write(\sprintf("\33[%dD", $columns));
        return $this;
    }
    public function moveToColumn(int $column) : self
    {
        $this->output->write(\sprintf("\33[%dG", $column));
        return $this;
    }
    public function moveToPosition(int $column, int $row) : self
    {
        $this->output->write(\sprintf("\33[%d;%dH", $row + 1, $column));
        return $this;
    }
    public function savePosition() : self
    {
        $this->output->write("\0337");
        return $this;
    }
    public function restorePosition() : self
    {
        $this->output->write("\338");
        return $this;
    }
    public function hide() : self
    {
        $this->output->write("\33[?25l");
        return $this;
    }
    public function show() : self
    {
        $this->output->write("\33[?25h\33[?0c");
        return $this;
    }
    /**
     * Clears all the output from the current line.
     */
    public function clearLine() : self
    {
        $this->output->write("\33[2K");
        return $this;
    }
    /**
     * Clears all the output from the current line after the current position.
     */
    public function clearLineAfter() : self
    {
        $this->output->write("\33[K");
        return $this;
    }
    /**
     * Clears all the output from the cursors' current position to the end of the screen.
     */
    public function clearOutput() : self
    {
        $this->output->write("\33[0J");
        return $this;
    }
    /**
     * Clears the entire screen.
     */
    public function clearScreen() : self
    {
        $this->output->write("\33[2J");
        return $this;
    }
    /**
     * Returns the current cursor position as x,y coordinates.
     */
    public function getCurrentPosition() : array
    {
        static $isTtySupported;
        if (null === $isTtySupported && \function_exists('proc_open')) {
            $isTtySupported = (bool) @\proc_open('echo 1 >/dev/null', [['file', '/dev/tty', 'r'], ['file', '/dev/tty', 'w'], ['file', '/dev/tty', 'w']], $pipes);
        }
        if (!$isTtySupported) {
            return [1, 1];
        }
        $sttyMode = \shell_exec('stty -g');
        \shell_exec('stty -icanon -echo');
        @\fwrite($this->input, "\33[6n");
        $code = \trim(\fread($this->input, 1024));
        \shell_exec(\sprintf('stty %s', $sttyMode));
        \sscanf($code, "\33[%d;%dR", $row, $col);
        return [$col, $row];
    }
}
