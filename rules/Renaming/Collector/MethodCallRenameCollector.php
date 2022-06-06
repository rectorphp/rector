<?php

declare (strict_types=1);
namespace Rector\Renaming\Collector;

use Rector\Renaming\Contract\MethodCallRenameInterface;
final class MethodCallRenameCollector
{
    /**
     * @var MethodCallRenameInterface[]
     */
    private $methodCallRenames = [];
    /**
     * @param MethodCallRenameInterface[] $methodCallRenames
     */
    public function addMethodCallRenames(array $methodCallRenames) : void
    {
        $this->methodCallRenames = \array_merge($this->methodCallRenames, $methodCallRenames);
    }
    /**
     * @return MethodCallRenameInterface[]
     */
    public function getMethodCallRenames() : array
    {
        return $this->methodCallRenames;
    }
}
