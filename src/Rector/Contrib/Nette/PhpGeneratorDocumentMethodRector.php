<?php declare(strict_types=1);

namespace Rector\Rector\Contrib\Nette;

use Rector\Deprecation\SetNames;
use Rector\Rector\AbstractChangeMethodNameRector;

final class PhpGeneratorDocumentMethodRector extends AbstractChangeMethodNameRector
{

    public function getSetName(): string
    {
        return SetNames::NETTE;
    }

    public function sinceVersion(): float
    {
        return 2.4;
    }

    protected function getClassName(): string
    {
        // allow array?
    }

    protected function getOldMethodName(): string
    {
        // return string
    }

    protected function getNewMethodName(): string
    {
        // retun string
    }

    /**
     * @return string[][]
     */
    protected function getPerClassOldToNewMethods(): array
    {
        // TODO: Implement getPerClassOldToNewMethods() method.
    }
}
