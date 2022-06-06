<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\FileSystemRector\Contract;

use RectorPrefix20220606\PhpParser\Node\Stmt;
interface FileWithNodesInterface
{
    /**
     * @return Stmt[]
     */
    public function getNodes() : array;
}
