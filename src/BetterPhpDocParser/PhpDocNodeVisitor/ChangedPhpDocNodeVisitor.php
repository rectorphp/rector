<?php

declare (strict_types=1);
namespace Rector\BetterPhpDocParser\PhpDocNodeVisitor;

use PHPStan\PhpDocParser\Ast\AbstractNodeVisitor;
use PHPStan\PhpDocParser\Ast\Node;
use Rector\BetterPhpDocParser\ValueObject\PhpDocAttributeKey;
final class ChangedPhpDocNodeVisitor extends AbstractNodeVisitor
{
    private bool $hasChanged = \false;
    public function beforeTraverse(array $nodes): ?array
    {
        $this->hasChanged = \false;
        return null;
    }
    public function enterNode(Node $node): ?Node
    {
        $origNode = $node->getAttribute(PhpDocAttributeKey::ORIG_NODE);
        if ($origNode === null) {
            $this->hasChanged = \true;
        }
        return null;
    }
    public function hasChanged(): bool
    {
        return $this->hasChanged;
    }
}
