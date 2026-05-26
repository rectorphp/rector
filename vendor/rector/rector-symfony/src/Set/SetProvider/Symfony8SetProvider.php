<?php

declare (strict_types=1);
namespace Rector\Symfony\Set\SetProvider;

use Rector\Set\Contract\SetInterface;
use Rector\Set\Contract\SetProviderInterface;
use Rector\Set\Enum\SetGroup;
use Rector\Set\ValueObject\ComposerTriggeredSet;
final class Symfony8SetProvider implements SetProviderInterface
{
    /**
     * @return SetInterface[]
     */
    public function provide(): array
    {
        return [new ComposerTriggeredSet(SetGroup::SYMFONY, 'symfony/security-core', '8.0', __DIR__ . '/../../../config/sets/symfony/symfony8/symfony80/symfony80-security-core.php'), new ComposerTriggeredSet(SetGroup::SYMFONY, 'symfony/dependency-injection', '8.1', __DIR__ . '/../../../config/sets/symfony/symfony8/symfony81/symfony81-dependency-injection.php'), new ComposerTriggeredSet(SetGroup::SYMFONY, 'symfony/uid', '8.1', __DIR__ . '/../../../config/sets/symfony/symfony8/symfony81/symfony81-uid.php'), new ComposerTriggeredSet(SetGroup::SYMFONY, 'symfony/serializer', '8.1', __DIR__ . '/../../../config/sets/symfony/symfony8/symfony81/symfony81-serializer.php')];
    }
}
