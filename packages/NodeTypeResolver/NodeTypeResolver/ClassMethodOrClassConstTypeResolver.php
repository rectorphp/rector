<?php

declare (strict_types=1);
namespace Rector\NodeTypeResolver\NodeTypeResolver;

use PhpParser\Node;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassConst;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Type\ObjectWithoutClassType;
use PHPStan\Type\Type;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\NodeTypeResolver\Contract\NodeTypeResolverInterface;
use Rector\NodeTypeResolver\NodeTypeResolver;
use RectorPrefix20211123\Symfony\Contracts\Service\Attribute\Required;
final class ClassMethodOrClassConstTypeResolver implements \Rector\NodeTypeResolver\Contract\NodeTypeResolverInterface
{
    /**
     * @var \Rector\NodeTypeResolver\NodeTypeResolver
     */
    private $nodeTypeResolver;
    /**
     * @var \Rector\Core\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    public function __construct(\Rector\Core\PhpParser\Node\BetterNodeFinder $betterNodeFinder)
    {
        $this->betterNodeFinder = $betterNodeFinder;
    }
    /**
     * @required
     */
    public function autowireClassMethodOrClassConstTypeResolver(\Rector\NodeTypeResolver\NodeTypeResolver $nodeTypeResolver) : void
    {
        $this->nodeTypeResolver = $nodeTypeResolver;
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeClasses() : array
    {
        return [\PhpParser\Node\Stmt\ClassMethod::class, \PhpParser\Node\Stmt\ClassConst::class];
    }
    /**
     * @param \PhpParser\Node $node
     */
    public function resolve($node) : \PHPStan\Type\Type
    {
        $classLike = $this->betterNodeFinder->findParentType($node, \PhpParser\Node\Stmt\ClassLike::class);
        if (!$classLike instanceof \PhpParser\Node\Stmt\ClassLike) {
            // anonymous class
            return new \PHPStan\Type\ObjectWithoutClassType();
        }
        return $this->nodeTypeResolver->getType($classLike);
    }
}
