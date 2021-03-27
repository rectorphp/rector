<?php

namespace Rector\Tests\DeadCode\Rector\Assign\RemoveUnusedVariableAssignRector\Fixture;

class SkipUsed
{
    public function run()
    {
        // run file node only if
        $fileNode = new FileNode($smartFileInfo, $parsedStmtsAndTokens->getNewStmts());
        $this->rectorNodeTraverser->traverseFileNode($fileNode);
    }
}