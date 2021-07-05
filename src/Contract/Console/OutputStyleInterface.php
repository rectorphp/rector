<?php

declare (strict_types=1);
namespace Rector\Core\Contract\Console;

interface OutputStyleInterface
{
    /**
     * @param string $message
     */
    public function error($message) : void;
    /**
     * @param string $message
     */
    public function success($message) : void;
    /**
     * @param string $message
     */
    public function warning($message) : void;
    /**
     * @param string $message
     */
    public function note($message) : void;
    /**
     * @param string $message
     */
    public function title($message) : void;
    /**
     * @param string $message
     */
    public function writeln($message) : void;
    /**
     * @param int $count
     */
    public function newline($count = 1) : void;
    /**
     * @param string[] $elements
     */
    public function listing($elements) : void;
}
