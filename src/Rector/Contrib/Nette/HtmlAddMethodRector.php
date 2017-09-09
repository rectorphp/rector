<?php declare(strict_types=1);

namespace Rector\Rector\Contrib\Nette;

use Rector\Rector\AbstractChangeMethodNameRector;

final class HtmlAddMethodRector extends AbstractChangeMethodNameRector
{
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
