<?php

declare (strict_types=1);
namespace Rector\Symfony\Set\SetProvider;

use Rector\Set\Contract\SetInterface;
use Rector\Set\Contract\SetProviderInterface;
use Rector\Set\Enum\SetGroup;
use Rector\Set\ValueObject\ComposerTriggeredSet;
final class Symfony7SetProvider implements SetProviderInterface
{
    /**
     * @return SetInterface[]
     */
    public function provide() : array
    {
        return [new ComposerTriggeredSet(SetGroup::SYMFONY, 'symfony/*', '7.0', __DIR__ . '/../../../config/sets/symfony/symfony70.php'), new ComposerTriggeredSet(SetGroup::SYMFONY, 'symfony/*', '7.1', __DIR__ . '/../../../config/sets/symfony/symfony71.php'), new ComposerTriggeredSet(SetGroup::SYMFONY, 'symfony/*', '7.2', __DIR__ . '/../../../config/sets/symfony/symfony72.php')];
    }
}
