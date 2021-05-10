<?php

declare (strict_types=1);
namespace Rector\BetterPhpDocParser\PhpDocNodeTraverser;

use Rector\BetterPhpDocParser\PhpDocNodeVisitor\ChangedPhpDocNodeVisitor;
use RectorPrefix20210510\Symplify\SimplePhpDocParser\PhpDocNodeTraverser;
final class ChangedPhpDocNodeTraverserFactory
{
    /**
     * @var ChangedPhpDocNodeVisitor
     */
    private $changedPhpDocNodeVisitor;
    public function __construct(ChangedPhpDocNodeVisitor $changedPhpDocNodeVisitor)
    {
        $this->changedPhpDocNodeVisitor = $changedPhpDocNodeVisitor;
    }
    public function create() : PhpDocNodeTraverser
    {
        $changedPhpDocNodeTraverser = new PhpDocNodeTraverser();
        $changedPhpDocNodeTraverser->addPhpDocNodeVisitor($this->changedPhpDocNodeVisitor);
        return $changedPhpDocNodeTraverser;
    }
}
