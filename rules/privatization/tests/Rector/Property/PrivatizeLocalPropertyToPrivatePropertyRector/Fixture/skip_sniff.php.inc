<?php

namespace Rector\Privatization\Tests\Rector\Property\PrivatizeLocalPropertyToPrivatePropertyRector\Fixture;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

final class SkipSniff implements Sniff
{
    public $sniffConfigProperty;

    public function shouldSkip(string $methodName, string $className): bool
    {
        // not really a setter, but usually test "setup" method
        if ($methodName === 'setUp') {
            return true;
        }

        foreach ($this->sniffConfigProperty as $allowedClass) {
            if (fnmatch($allowedClass, $className, FNM_NOESCAPE)) {
                return true;
            }
        }

        return false;
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
