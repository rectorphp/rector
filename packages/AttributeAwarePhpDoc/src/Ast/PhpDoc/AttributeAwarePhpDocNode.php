<?php

declare(strict_types=1);

namespace Rector\AttributeAwarePhpDoc\Ast\PhpDoc;

use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocChildNode;
use Rector\BetterPhpDocParser\Contract\PhpDocNode\AttributeAwareNodeInterface;

final class AttributeAwarePhpDocNode extends \PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocNode implements \Rector\BetterPhpDocParser\Contract\PhpDocNode\AttributeAwareNodeInterface
{
    use \Rector\BetterPhpDocParser\Attributes\Attribute\AttributeTrait;

    /**
     * @var PhpDocChildNode[]|AttributeAwareNodeInterface[]
     */
    public $children = [];

    public function __toString(): string
    {
        return "/**\n * " . implode("\n * ", $this->children) . "\n */";
    }
}
