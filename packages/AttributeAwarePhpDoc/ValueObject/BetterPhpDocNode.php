<?php

declare(strict_types=1);

namespace Rector\AttributeAwarePhpDoc\ValueObject;

use PHPStan\PhpDocParser\Ast\BaseNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocChildNode;
use Symplify\SimplePhpDocParser\ValueObject\Ast\PhpDoc\SimplePhpDocNode;

final class BetterPhpDocNode extends SimplePhpDocNode
{
    /**
     * @var array<PhpDocChildNode&BaseNode>
     */
    public $children = [];

    public function __toString(): string
    {
        return "/**\n * " . implode("\n * ", $this->children) . "\n */";
    }
}
