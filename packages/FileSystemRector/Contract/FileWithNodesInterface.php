<?php

declare(strict_types=1);

namespace Rector\FileSystemRector\Contract;

use PhpParser\Node;

interface FileWithNodesInterface
{
    /**
     * @return Node[]
     */
    public function getNodes(): array;
}
