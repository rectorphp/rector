<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\NodeAnalyzer;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ClassConstFetch;
use Rector\NodeNameResolver\NodeNameResolver;
final class ClassConstAnalyzer
{
    /**
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    public function __construct(\Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver)
    {
        $this->nodeNameResolver = $nodeNameResolver;
    }
    /**
     * Detects "SomeClass::class"
     */
    public function isClassConstReference(\PhpParser\Node\Expr $expr, string $className) : bool
    {
        if (!$expr instanceof \PhpParser\Node\Expr\ClassConstFetch) {
            return \false;
        }
        if (!$this->nodeNameResolver->isName($expr->name, 'class')) {
            return \false;
        }
        return $this->nodeNameResolver->isName($expr->class, $className);
    }
}
