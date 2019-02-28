<?php declare(strict_types=1);

namespace Rector\BetterPhpDocParser\Contract;

use PhpParser\Node;
use Rector\BetterPhpDocParser\Attributes\Ast\PhpDoc\AttributeAwarePhpDocNode;

interface PhpDocNodeDecoratorInterface
{
    public function decorate(AttributeAwarePhpDocNode $attributeAwarePhpDocNode, Node $node): AttributeAwarePhpDocNode;
}
