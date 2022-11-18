<?php

declare (strict_types=1);
namespace Rector\Renaming\Collector;

use Rector\Renaming\ValueObject\MethodCallRename;
final class MethodCallRenameCollector
{
    /**
     * @var MethodCallRename[]
     */
    private $methodCallRenames = [];
    /**
     * @param MethodCallRename[] $methodCallRenames
     */
    public function addMethodCallRenames(array $methodCallRenames) : void
    {
        $this->methodCallRenames = \array_merge($this->methodCallRenames, $methodCallRenames);
    }
    /**
     * @return MethodCallRename[]
     */
    public function getMethodCallRenames() : array
    {
        return $this->methodCallRenames;
    }
}
