<?php

declare (strict_types=1);
namespace Rector\BetterPhpDocParser\PhpDocNodeTraverser;

use Rector\BetterPhpDocParser\PhpDocNodeVisitor\ChangedPhpDocNodeVisitor;
use RectorPrefix20211020\Symplify\SimplePhpDocParser\PhpDocNodeTraverser;
final class ChangedPhpDocNodeTraverserFactory
{
    /**
     * @var \Rector\BetterPhpDocParser\PhpDocNodeVisitor\ChangedPhpDocNodeVisitor
     */
    private $changedPhpDocNodeVisitor;
    public function __construct(\Rector\BetterPhpDocParser\PhpDocNodeVisitor\ChangedPhpDocNodeVisitor $changedPhpDocNodeVisitor)
    {
        $this->changedPhpDocNodeVisitor = $changedPhpDocNodeVisitor;
    }
    public function create() : \RectorPrefix20211020\Symplify\SimplePhpDocParser\PhpDocNodeTraverser
    {
        $changedPhpDocNodeTraverser = new \RectorPrefix20211020\Symplify\SimplePhpDocParser\PhpDocNodeTraverser();
        $changedPhpDocNodeTraverser->addPhpDocNodeVisitor($this->changedPhpDocNodeVisitor);
        return $changedPhpDocNodeTraverser;
    }
}
