<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Doctrine\Property_;

use Rector\BetterPhpDocParser\Contract\PhpDocNode\SilentKeyNodeInterface;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Doctrine\AbstractDoctrineTagValueNode;

/**
 * @see \Rector\Tests\BetterPhpDocParser\PhpDocParser\TagValueNodeReprint\TagValueNodeReprintTest
 */
final class GeneratedValueTagValueNode extends AbstractDoctrineTagValueNode implements SilentKeyNodeInterface
{
    public function getShortName(): string
    {
        return '@ORM\GeneratedValue';
    }

    public function getSilentKey(): string
    {
        return 'strategy';
    }
}
