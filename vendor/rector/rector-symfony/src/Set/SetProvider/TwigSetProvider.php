<?php

declare (strict_types=1);
namespace Rector\Symfony\Set\SetProvider;

use Rector\Set\Contract\SetInterface;
use Rector\Set\Contract\SetProviderInterface;
use Rector\Set\ValueObject\ComposerTriggeredSet;
use Rector\Symfony\Set\TwigSetList;
final class TwigSetProvider implements SetProviderInterface
{
    /**
     * @return SetInterface[]
     */
    public function provide() : array
    {
        return [new ComposerTriggeredSet('twig', 'twig/twig', '1.12', TwigSetList::TWIG_112), new ComposerTriggeredSet('twig', 'twig/twig', '1.27', TwigSetList::TWIG_127), new ComposerTriggeredSet('twig', 'twig/twig', '1.34', TwigSetList::TWIG_134), new ComposerTriggeredSet('twig', 'twig/twig', '1.40', TwigSetList::TWIG_140), new ComposerTriggeredSet('twig', 'twig/twig', '2.0', TwigSetList::TWIG_20), new ComposerTriggeredSet('twig', 'twig/twig', '2.40', TwigSetList::TWIG_240)];
    }
}
