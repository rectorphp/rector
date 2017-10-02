<?php declare(strict_types=1);

namespace Rector\Rector\Contrib\Nette\Image;

use Rector\Rector\AbstractClassReplacerRector;

final class NetteImageRector extends AbstractClassReplacerRector
{
    /**
     * @return string[]
     */
    protected function getOldToNewClasses(): array
    {
        return [
            'Image' => 'Nette\Image',
        ];
    }
}
