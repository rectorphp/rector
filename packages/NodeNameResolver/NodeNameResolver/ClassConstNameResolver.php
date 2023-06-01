<?php

declare (strict_types=1);
namespace Rector\NodeNameResolver\NodeNameResolver;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassConst;
use PHPStan\Analyser\Scope;
use Rector\NodeNameResolver\Contract\NodeNameResolverInterface;
use Rector\NodeNameResolver\NodeNameResolver;
use RectorPrefix202306\Symfony\Contracts\Service\Attribute\Required;
/**
 * @implements NodeNameResolverInterface<ClassConst>
 */
final class ClassConstNameResolver implements NodeNameResolverInterface
{
    /**
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @required
     */
    public function autowire(NodeNameResolver $nodeNameResolver) : void
    {
        $this->nodeNameResolver = $nodeNameResolver;
    }
    public function getNode() : string
    {
        return ClassConst::class;
    }
    /**
     * @param ClassConst $node
     */
    public function resolve(Node $node, ?Scope $scope) : ?string
    {
        if ($node->consts === []) {
            return null;
        }
        $onlyConstant = $node->consts[0];
        return $this->nodeNameResolver->getName($onlyConstant);
    }
}
