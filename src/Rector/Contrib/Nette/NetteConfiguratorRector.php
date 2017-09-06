<?php declare(strict_types=1);

namespace Rector\Rector\Contrib\Nette;

use Rector\Deprecation\SetNames;
use Rector\Rector\AbstractClassReplacerRector;

final class NetteConfiguratorRector extends AbstractClassReplacerRector
{
    public function getSetName(): string
    {
        return SetNames::NETTE;
    }

    public function sinceVersion(): float
    {
        return 2.1;
    }

    /**
     * @return string[]
     */
    protected function getOldToNewClasses(): array
    {
        return [
            'Nette\Config\Configurator' => 'Nette\Configurator'
        ];
    }
}
