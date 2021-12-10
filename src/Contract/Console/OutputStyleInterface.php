<?php

declare (strict_types=1);
namespace Rector\Core\Contract\Console;

interface OutputStyleInterface
{
    public function error(string $message) : void;
    public function success(string $message) : void;
    public function warning(string $message) : void;
    public function note(string $message) : void;
    public function title(string $message) : void;
    public function writeln(string $message) : void;
    public function newline(int $count = 1) : void;
    /**
     * @param string[] $elements
     */
    public function listing(array $elements) : void;
}
