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
        return [new ComposerTriggeredSet(SetGroup::SYMFONY, 'symfony/symfony', '7.0', __DIR__ . '/../../../config/sets/symfony/symfony7/symfony70.php'), new ComposerTriggeredSet(SetGroup::SYMFONY, 'symfony/dependency-injection', '7.0', __DIR__ . '/../../../config/sets/symfony/symfony7/symfony70/symfony70-dependency-injection.php'), new ComposerTriggeredSet(SetGroup::SYMFONY, 'symfony/serializer', '7.0', __DIR__ . '/../../../config/sets/symfony/symfony7/symfony70/symfony70-serializer.php'), new ComposerTriggeredSet(SetGroup::SYMFONY, 'symfony/http-foundation', '7.0', __DIR__ . '/../../../config/sets/symfony/symfony7/symfony70/symfony70-http-foundation.php'), new ComposerTriggeredSet(SetGroup::SYMFONY, 'symfony/contracts', '7.0', __DIR__ . '/../../../config/sets/symfony/symfony7/symfony70/symfony70-contracts.php'), new ComposerTriggeredSet(SetGroup::SYMFONY, 'symfony/symfony', '7.1', __DIR__ . '/../../../config/sets/symfony/symfony7/symfony71.php'), new ComposerTriggeredSet(SetGroup::SYMFONY, 'symfony/dependency-injection', '7.1', __DIR__ . '/../../../config/sets/symfony/symfony7/symfony71/symfony71-dependency-injection.php'), new ComposerTriggeredSet(SetGroup::SYMFONY, 'symfony/serializer', '7.1', __DIR__ . '/../../../config/sets/symfony/symfony7/symfony71/symfony71-serializer.php'), new ComposerTriggeredSet(SetGroup::SYMFONY, 'symfony/symfony', '7.2', __DIR__ . '/../../../config/sets/symfony/symfony7/symfony72.php'), new ComposerTriggeredSet(SetGroup::SYMFONY, 'symfony/serializer', '7.2', __DIR__ . '/../../../config/sets/symfony/symfony7/symfony72/symfony72-serializer.php'), new ComposerTriggeredSet(SetGroup::SYMFONY, 'symfony/symfony', '7.3', __DIR__ . '/../../../config/sets/symfony/symfony7/symfony73.php'), new ComposerTriggeredSet(SetGroup::SYMFONY, 'symfony/console', '7.3', __DIR__ . '/../../../config/sets/symfony/symfony7/symfony73/symfony73-console.php'), new ComposerTriggeredSet(SetGroup::SYMFONY, 'symfony/twig-bundle', '7.3', __DIR__ . '/../../../config/sets/symfony/symfony7/symfony73/symfony73-twig-bundle.php')];
    }
}
