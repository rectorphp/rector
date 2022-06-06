<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Ssch\TYPO3Rector\NodeAnalyzer;

use RectorPrefix20220606\PhpParser\Node\Expr;
use RectorPrefix20220606\PhpParser\Node\Expr\ClassConstFetch;
use RectorPrefix20220606\Rector\NodeNameResolver\NodeNameResolver;
final class ClassConstAnalyzer
{
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    public function __construct(NodeNameResolver $nodeNameResolver)
    {
        $this->nodeNameResolver = $nodeNameResolver;
    }
    /**
     * Detects "SomeClass::class"
     */
    public function isClassConstReference(Expr $expr, string $className) : bool
    {
        if (!$expr instanceof ClassConstFetch) {
            return \false;
        }
        if (!$this->nodeNameResolver->isName($expr->name, 'class')) {
            return \false;
        }
        return $this->nodeNameResolver->isName($expr->class, $className);
    }
}
