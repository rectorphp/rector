<?php

declare (strict_types=1);
namespace Rector\NodeTypeResolver\NodeTypeResolver;

use PhpParser\Node;
use PhpParser\Node\Param;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use Rector\NodeTypeResolver\Contract\NodeTypeResolverInterface;
use Rector\NodeTypeResolver\NodeTypeResolver;
use RectorPrefix202307\Symfony\Contracts\Service\Attribute\Required;
/**
 * @see \Rector\Tests\NodeTypeResolver\PerNodeTypeResolver\ParamTypeResolver\ParamTypeResolverTest
 *
 * @implements NodeTypeResolverInterface<Param>
 */
final class ParamTypeResolver implements NodeTypeResolverInterface
{
    /**
     * @var \Rector\NodeTypeResolver\NodeTypeResolver
     */
    private $nodeTypeResolver;
    /**
     * @required
     */
    public function autowire(NodeTypeResolver $nodeTypeResolver) : void
    {
        $this->nodeTypeResolver = $nodeTypeResolver;
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeClasses() : array
    {
        return [Param::class];
    }
    /**
     * @param Param $node
     */
    public function resolve(Node $node) : Type
    {
        if ($node->type === null) {
            return new MixedType();
        }
        return $this->nodeTypeResolver->getType($node->type);
    }
}
