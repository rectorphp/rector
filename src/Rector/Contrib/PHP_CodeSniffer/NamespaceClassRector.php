<?php declare(strict_types=1);

namespace Rector\Rector\Contrib\PHP_CodeSniffer;

use Rector\Rector\AbstractClassReplacerRector;

/**
 * Covers https://github.com/squizlabs/PHP_CodeSniffer/wiki/Version-3.0-Upgrade-Guide
 */
final class NamespaceClassRector extends AbstractClassReplacerRector
{
    /**
     * @return string[]
     */
    protected function getOldToNewClasses(): array
    {
        return [
            'PHP_CodeSniffer_Sniffs_Sniff' => 'PHP_CodeSniffer\Sniffs\Sniff',
            'PHP_CodeSniffer_File' => 'PHP_CodeSniffer\Files\File',
            'PHP_CodeSniffer_Tokens' => 'PHP_CodeSniffer\Util\Tokens',
            'StandardName_Tests_Category_TestSniffUnitTest' => 'PHP_CodeSniffer\Tests\Standards\AbstractSniffUnitTest',
        ];
    }
}
