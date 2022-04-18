<?php

declare (strict_types=1);
namespace Rector\BetterPhpDocParser\PhpDocNodeTraverser;

use Rector\BetterPhpDocParser\PhpDocNodeVisitor\ChangedPhpDocNodeVisitor;
use RectorPrefix20220418\Symplify\Astral\PhpDocParser\PhpDocNodeTraverser;
final class ChangedPhpDocNodeTraverserFactory
{
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocNodeVisitor\ChangedPhpDocNodeVisitor
     */
    private $changedPhpDocNodeVisitor;
    public function __construct(\Rector\BetterPhpDocParser\PhpDocNodeVisitor\ChangedPhpDocNodeVisitor $changedPhpDocNodeVisitor)
    {
        $this->changedPhpDocNodeVisitor = $changedPhpDocNodeVisitor;
    }
    public function create() : \RectorPrefix20220418\Symplify\Astral\PhpDocParser\PhpDocNodeTraverser
    {
        $changedPhpDocNodeTraverser = new \RectorPrefix20220418\Symplify\Astral\PhpDocParser\PhpDocNodeTraverser();
        $changedPhpDocNodeTraverser->addPhpDocNodeVisitor($this->changedPhpDocNodeVisitor);
        return $changedPhpDocNodeTraverser;
    }
}
