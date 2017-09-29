<?php declare(strict_types=1);

namespace Rector\Rector\Contrib\Nette\Environment;

use Rector\Rector\AbstractClassReplacerRector;

final class NetteEnvironmentRector extends AbstractClassReplacerRector
{
    /**
     * @return string[]
     */
    protected function getOldToNewClasses(): array
    {
        return [
            'Environment' => 'Nette\Environment',
        ];
    }
}
