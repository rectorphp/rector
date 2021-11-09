<?php

declare (strict_types=1);
namespace Rector\DependencyInjection\NodeAnalyzer;

use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\NodeNameResolver\NodeNameResolver;
final class ControllerClassMethodAnalyzer
{
    /**
     * @var \Rector\Core\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    /**
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    public function __construct(\Rector\Core\PhpParser\Node\BetterNodeFinder $betterNodeFinder, \Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver)
    {
        $this->betterNodeFinder = $betterNodeFinder;
        $this->nodeNameResolver = $nodeNameResolver;
    }
    public function isInControllerActionMethod(\PhpParser\Node\Expr\Variable $variable) : bool
    {
        $class = $this->betterNodeFinder->findParentType($variable, \PhpParser\Node\Stmt\Class_::class);
        if (!$class instanceof \PhpParser\Node\Stmt\Class_) {
            return \false;
        }
        $className = $this->nodeNameResolver->getName($class);
        if (!\is_string($className)) {
            return \false;
        }
        if (\substr_compare($className, 'Controller', -\strlen('Controller')) !== 0) {
            return \false;
        }
        $classMethod = $this->betterNodeFinder->findParentType($variable, \PhpParser\Node\Stmt\ClassMethod::class);
        if (!$classMethod instanceof \PhpParser\Node\Stmt\ClassMethod) {
            return \false;
        }
        // is probably in controller action
        return $classMethod->isPublic();
    }
}
