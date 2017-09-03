<?php declare(strict_types=1);

namespace Rector\Rector\Contrib\PHP_CodeSniffer;

use Rector\Deprecation\SetNames;
use Rector\Rector\AbstractClassReplacerRector;

final class NamespaceClassRector extends AbstractClassReplacerRector
{
    public function getSetName(): string
    {
        return SetNames::PHP_CODE_SNIFER;
    }

    public function sinceVersion(): float
    {
        return 3.0;
    }

    /**
     * @return string[]
     */
    protected function getOldToNewClasses(): array
    {
        return [
            'PHP_CodeSniffer_Sniffs_Sniff' => 'PHP_CodeSniffer\Sniffs\Sniff',
        ];
    }
}
