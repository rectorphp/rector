<?php declare(strict_types=1);

namespace Rector\DoctrinePhpDocParser\Ast\PhpDoc;

use Rector\BetterPhpDocParser\PhpDocParser\Ast\PhpDoc\AbstractTagValueNode;
use Rector\DoctrinePhpDocParser\Contract\Ast\PhpDoc\DoctrineTagNodeInterface;

abstract class AbstractDoctrineTagValueNode extends AbstractTagValueNode implements DoctrineTagNodeInterface
{
}
