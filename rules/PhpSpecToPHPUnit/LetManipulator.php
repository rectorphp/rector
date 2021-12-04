<?php

declare (strict_types=1);
namespace Rector\PhpSpecToPHPUnit;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Stmt\Class_;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\NodeNameResolver\NodeNameResolver;
final class LetManipulator
{
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    public function __construct(\Rector\Core\PhpParser\Node\BetterNodeFinder $betterNodeFinder, \Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver)
    {
        $this->betterNodeFinder = $betterNodeFinder;
        $this->nodeNameResolver = $nodeNameResolver;
    }
    public function isLetNeededInClass(\PhpParser\Node\Stmt\Class_ $class) : bool
    {
        foreach ($class->getMethods() as $classMethod) {
            // new test
            if ($this->nodeNameResolver->isName($classMethod, 'test*')) {
                continue;
            }
            $hasBeConstructedThrough = (bool) $this->betterNodeFinder->find((array) $classMethod->stmts, function (\PhpParser\Node $node) : ?bool {
                if (!$node instanceof \PhpParser\Node\Expr\MethodCall) {
                    return null;
                }
                return $this->nodeNameResolver->isName($node->name, 'beConstructedThrough');
            });
            if ($hasBeConstructedThrough) {
                continue;
            }
            return \true;
        }
        return \false;
    }
}
