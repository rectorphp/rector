<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocNode\Doctrine\Property_;

use Rector\BetterPhpDocParser\PhpDocNode\Doctrine\AbstractDoctrineTagValueNode;

final class IdTagValueNode extends AbstractDoctrineTagValueNode
{
    public function __construct(?string $annotationContent = null)
    {
        $this->resolveOriginalContentSpacingAndOrder($annotationContent);
    }

    public function __toString(): string
    {
        $content = '';

        if ($this->hasOpeningBracket) {
            $content .= '(';
        }

        if ($this->hasClosingBracket) {
            $content .= ')';
        }

        return $content;
    }

    public function getShortName(): string
    {
        return '@ORM\Id';
    }
}
