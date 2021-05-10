<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocNodeTraverser;

use Rector\BetterPhpDocParser\PhpDocNodeVisitor\ChangedPhpDocNodeVisitor;
use Symplify\SimplePhpDocParser\PhpDocNodeTraverser;

final class ChangedPhpDocNodeTraverserFactory
{
    public function __construct(
        private ChangedPhpDocNodeVisitor $changedPhpDocNodeVisitor
    ) {
    }

    public function create(): PhpDocNodeTraverser
    {
        $changedPhpDocNodeTraverser = new PhpDocNodeTraverser();
        $changedPhpDocNodeTraverser->addPhpDocNodeVisitor($this->changedPhpDocNodeVisitor);

        return $changedPhpDocNodeTraverser;
    }
}
