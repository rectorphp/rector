<?php

namespace Rector\Renaming\Collector;

use Rector\Renaming\Contract\MethodCallRenameInterface;

final class MethodCallRenameCollector
{
    /**
     * @var MethodCallRenameInterface[]
     */
    private $methodCallRenames = [];

    public function addMethodCallRename(MethodCallRenameInterface $methodCallRename): void
    {
        $this->methodCallRenames[] = $methodCallRename;
    }

    /**
     * @return MethodCallRenameInterface[]
     */
    public function getMethodCallRenames(): array
    {
        return $this->methodCallRenames;
    }
}
