<?php

declare (strict_types=1);
namespace Rector\Doctrine\Set\SetProvider;

use Rector\Set\Contract\SetInterface;
use Rector\Set\Contract\SetProviderInterface;
use Rector\Set\ValueObject\ComposerTriggeredSet;
/**
 * @api collected in core
 */
final class DoctrineSetProvider implements SetProviderInterface
{
    /**
     * @return SetInterface[]
     */
    public function provide() : array
    {
        return [new ComposerTriggeredSet('doctrine', 'doctrine/common', '2.10', __DIR__ . '/../../../config/sets/doctrine-common-20.php')];
    }
}
