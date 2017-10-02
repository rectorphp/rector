<?php declare(strict_types=1);

namespace Rector\Rector\Contrib\Nette\Naming;

use Rector\Rector\AbstractClassReplacerRector;

final class ClassNaming20Rector extends AbstractClassReplacerRector
{
    /**
     * @return string[]
     */
    protected function getOldToNewClasses(): array
    {
        return [
            'Environment' => 'Nette\Environment',
            'Image' => 'Nette\Image',
        ];
    }
}
