<?php declare(strict_types=1);

namespace Rector\Rector\Contrib\Nette;

use Rector\Deprecation\SetNames;
use Rector\Rector\AbstractChangeMethodNameRector;

final class HtmlAddMethodRector extends AbstractChangeMethodNameRector
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
        return 'Nette\Utils\Html';
    }

    protected function getOldMethodName(): string
    {
        return 'add';
    }

    protected function getNewMethodName(): string
    {
        return 'addHtml';
    }
}
