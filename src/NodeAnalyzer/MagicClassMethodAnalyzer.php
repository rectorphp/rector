<?php

declare (strict_types=1);
namespace Rector\NodeAnalyzer;

use PhpParser\Node\Stmt\ClassMethod;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\ValueObject\MethodName;
final class MagicClassMethodAnalyzer
{
    /**
     * @readonly
     */
    private NodeNameResolver $nodeNameResolver;
    public function __construct(NodeNameResolver $nodeNameResolver)
    {
        $this->nodeNameResolver = $nodeNameResolver;
    }
    public function isUnsafeOverridden(ClassMethod $classMethod) : bool
    {
        if ($this->nodeNameResolver->isName($classMethod, MethodName::INVOKE)) {
            return \false;
        }
        return $classMethod->isMagic();
    }
}
