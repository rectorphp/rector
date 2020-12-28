<?php

namespace Rector\Privatization\Tests\Rector\Property\ChangeReadOnlyPropertyWithDefaultValueToConstantRector\Fixture;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

final class SkipSniff implements Sniff
{
    /**
     * @var string[]
     */
    private $magicMethods = [
    ];

    public function run()
    {
        foreach ($this->magicMethods as $magicMethod) {
            echo $magicMethod;
        }
    }

    public function register()
    {
        // TODO: Implement register() method.
    }

    public function process(File $phpcsFile, $stackPtr)
    {
        // TODO: Implement process() method.
    }
}
