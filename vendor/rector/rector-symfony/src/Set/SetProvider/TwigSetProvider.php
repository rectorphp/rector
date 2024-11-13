<?php

declare (strict_types=1);
namespace Rector\Symfony\Set\SetProvider;

use Rector\Set\Contract\SetInterface;
use Rector\Set\Contract\SetProviderInterface;
use Rector\Set\Enum\SetGroup;
use Rector\Set\ValueObject\ComposerTriggeredSet;
final class TwigSetProvider implements SetProviderInterface
{
    /**
     * @return SetInterface[]
     */
    public function provide() : array
    {
        return [new ComposerTriggeredSet(SetGroup::TWIG, 'twig/twig', '1.12', __DIR__ . '/../../../config/sets/twig/twig112.php'), new ComposerTriggeredSet(SetGroup::TWIG, 'twig/twig', '1.27', __DIR__ . '/../../../config/sets/twig/twig127.php'), new ComposerTriggeredSet(SetGroup::TWIG, 'twig/twig', '1.34', __DIR__ . '/../../../config/sets/twig/twig134.php'), new ComposerTriggeredSet(SetGroup::TWIG, 'twig/twig', '1.40', __DIR__ . '/../../../config/sets/twig/twig140.php'), new ComposerTriggeredSet(SetGroup::TWIG, 'twig/twig', '2.0', __DIR__ . '/../../../config/sets/twig/twig20.php'), new ComposerTriggeredSet(SetGroup::TWIG, 'twig/twig', '2.4', __DIR__ . '/../../../config/sets/twig/twig24.php')];
    }
}
