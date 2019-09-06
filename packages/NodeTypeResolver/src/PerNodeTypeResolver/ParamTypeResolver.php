<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\PerNodeTypeResolver;

use PhpParser\Node;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Param;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use Rector\NodeTypeResolver\Contract\PerNodeTypeResolver\PerNodeTypeResolverInterface;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\PhpDoc\NodeAnalyzer\DocBlockManipulator;
use Rector\PhpParser\Node\Resolver\NameResolver;

/**
 * @see \Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\ParamTypeResolver\ParamTypeResolverTest
 */
final class ParamTypeResolver implements PerNodeTypeResolverInterface
{
    /**
     * @var NameResolver
     */
    private $nameResolver;

    /**
     * @var DocBlockManipulator
     */
    private $docBlockManipulator;

    public function __construct(NameResolver $nameResolver, DocBlockManipulator $docBlockManipulator)
    {
        $this->nameResolver = $nameResolver;
        $this->docBlockManipulator = $docBlockManipulator;
    }

    /**
     * @return string[]
     */
    public function getNodeClasses(): array
    {
        return [Param::class];
    }

    /**
     * @param Param $paramNode
     */
    public function resolve(Node $paramNode): Type
    {
        if ($paramNode->type !== null) {
            $resolveTypeName = $this->nameResolver->getName($paramNode->type);
            if ($resolveTypeName) {
                // @todo map the other way every type :)
                return new ObjectType($resolveTypeName);
            }
        }

        /** @var FunctionLike $parentNode */
        $parentNode = $paramNode->getAttribute(AttributeKey::PARENT_NODE);

        return $this->resolveTypesFromFunctionDocBlock($paramNode, $parentNode);
    }

    private function resolveTypesFromFunctionDocBlock(Param $param, FunctionLike $functionLike): Type
    {
        /** @var string $paramName */
        $paramName = $this->nameResolver->getName($param);

        return $this->docBlockManipulator->getParamTypeByName($functionLike, $paramName);
    }
}
