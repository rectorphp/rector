<?php

declare (strict_types=1);
namespace Rector\DependencyInjection\NodeAnalyzer;

use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\NodeTypeResolver\Node\AttributeKey;
final class ControllerClassMethodAnalyzer
{
    /**
     * @var \Rector\Core\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    public function __construct(\Rector\Core\PhpParser\Node\BetterNodeFinder $betterNodeFinder)
    {
        $this->betterNodeFinder = $betterNodeFinder;
    }
    public function isInControllerActionMethod(\PhpParser\Node\Expr\Variable $variable) : bool
    {
        /** @var string|null $className */
        $className = $variable->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::CLASS_NAME);
        if ($className === null) {
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
