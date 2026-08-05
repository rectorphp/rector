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
    public function provide(): array
    {
        return [
            // the composer-based set holds rules bound to the exact Twig package version they are available from
            new ComposerTriggeredSet(SetGroup::TWIG, 'twig/twig', '>=1.12', __DIR__ . '/../../../config/sets/twig/composer-based.php'),
        ];
    }
}
