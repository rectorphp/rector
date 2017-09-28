<?php declare(strict_types=1);

namespace Rector\Rector\Contrib\Nette\Bootstrap;

use Rector\Rector\AbstractClassReplacerRector;

final class NetteConfiguratorRector extends AbstractClassReplacerRector
{
    /**
     * @return string[]
     */
    protected function getOldToNewClasses(): array
    {
        return [
            'Nette\Config\Configurator' => 'Nette\Configurator',
        ];
    }
}
