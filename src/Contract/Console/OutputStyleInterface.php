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
    public function newLine(int $count = 1) : void;
    /**
     * @param string[] $elements
     */
    public function listing(array $elements) : void;
    public function isVerbose() : bool;
    public function isDebug() : bool;
    public function setVerbosity(int $level) : void;
    public function progressStart(int $fileCount) : void;
    public function progressAdvance(int $step = 1) : void;
}
