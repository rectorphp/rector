<?php declare(strict_types=1);

namespace Rector\DoctrinePhpDocParser\Ast\PhpDoc\Property_;

use Rector\DoctrinePhpDocParser\Ast\PhpDoc\AbstractDoctrineTagValueNode;

final class IdTagValueNode extends AbstractDoctrineTagValueNode
{
    public function __toString(): string
    {
        return '';
    }
}
