<?php

declare (strict_types=1);
namespace Rector\BetterPhpDocParser\PhpDocNodeVisitor;

use PHPStan\PhpDocParser\Ast\Node;
use Rector\BetterPhpDocParser\ValueObject\PhpDocAttributeKey;
use Rector\PhpDocParser\PhpDocParser\PhpDocNodeVisitor\AbstractPhpDocNodeVisitor;
final class ChangedPhpDocNodeVisitor extends AbstractPhpDocNodeVisitor
{
    private bool $hasChanged = \false;
    public function beforeTraverse(Node $node) : void
    {
        $this->hasChanged = \false;
    }
    public function enterNode(Node $node) : ?Node
    {
        $origNode = $node->getAttribute(PhpDocAttributeKey::ORIG_NODE);
        if ($origNode === null) {
            $this->hasChanged = \true;
        }
        return null;
    }
    public function hasChanged() : bool
    {
        return $this->hasChanged;
    }
}
