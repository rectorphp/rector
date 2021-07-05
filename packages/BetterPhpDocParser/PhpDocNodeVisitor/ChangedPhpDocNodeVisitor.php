<?php

declare (strict_types=1);
namespace Rector\BetterPhpDocParser\PhpDocNodeVisitor;

use PHPStan\PhpDocParser\Ast\Node;
use Rector\BetterPhpDocParser\ValueObject\PhpDocAttributeKey;
use RectorPrefix20210705\Symplify\SimplePhpDocParser\PhpDocNodeVisitor\AbstractPhpDocNodeVisitor;
final class ChangedPhpDocNodeVisitor extends \RectorPrefix20210705\Symplify\SimplePhpDocParser\PhpDocNodeVisitor\AbstractPhpDocNodeVisitor
{
    /**
     * @var bool
     */
    private $hasChanged = \false;
    /**
     * @param \PHPStan\PhpDocParser\Ast\Node $node
     */
    public function beforeTraverse($node) : void
    {
        $this->hasChanged = \false;
    }
    /**
     * @param \PHPStan\PhpDocParser\Ast\Node $node
     */
    public function enterNode($node) : ?\PHPStan\PhpDocParser\Ast\Node
    {
        $origNode = $node->getAttribute(\Rector\BetterPhpDocParser\ValueObject\PhpDocAttributeKey::ORIG_NODE);
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
