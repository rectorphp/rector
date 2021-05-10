<?php

declare (strict_types=1);
namespace Rector\FileSystemRector\Contract;

use PhpParser\Node\Stmt;
interface FileWithNodesInterface
{
    /**
     * @return Stmt[]
     */
    public function getNodes() : array;
}
