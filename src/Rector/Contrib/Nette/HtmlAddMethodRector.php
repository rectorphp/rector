<?php declare(strict_types=1);

namespace Rector\Rector\Contrib\Nette;

use Rector\Rector\AbstractChangeMethodNameRector;
use Rector\Rector\Set\SetNames;

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

    /**
     * @return string[][]
     */
    protected function getPerClassOldToNewMethods(): array
    {
        return [
            'Nette\Utils\Html' => [
                'add' => 'addHtml',
            ],
        ];
    }
}
