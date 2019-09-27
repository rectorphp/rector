<?php declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocNode\Doctrine;

use Rector\BetterPhpDocParser\Contract\Doctrine\DoctrineTagNodeInterface;
use Rector\BetterPhpDocParser\PhpDocNode\AbstractTagValueNode;

abstract class AbstractDoctrineTagValueNode extends AbstractTagValueNode implements DoctrineTagNodeInterface
{
}
