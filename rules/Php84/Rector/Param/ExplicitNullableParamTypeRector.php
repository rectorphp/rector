<?php

declare (strict_types=1);
namespace Rector\Php84\Rector\Param;

use PhpParser\Node;
use PhpParser\Node\ComplexType;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\IntersectionType;
use PhpParser\Node\Name;
use PhpParser\Node\NullableType;
use PhpParser\Node\Param;
use PhpParser\Node\UnionType;
use PHPStan\Type\MixedType;
use PHPStan\Type\TypeCombinator;
use Rector\PhpParser\Node\Value\ValueResolver;
use Rector\PHPStanStaticTypeMapper\Enum\TypeKind;
use Rector\Rector\AbstractRector;
use Rector\StaticTypeMapper\StaticTypeMapper;
use Rector\ValueObject\PhpVersionFeature;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\Php84\Rector\Param\ExplicitNullableParamTypeRector\ExplicitNullableParamTypeRectorTest
 */
final class ExplicitNullableParamTypeRector extends AbstractRector implements MinPhpVersionInterface
{
    /**
     * @readonly
     * @var \Rector\PhpParser\Node\Value\ValueResolver
     */
    private $valueResolver;
    /**
     * @readonly
     * @var \Rector\StaticTypeMapper\StaticTypeMapper
     */
    private $staticTypeMapper;
    public function __construct(ValueResolver $valueResolver, StaticTypeMapper $staticTypeMapper)
    {
        $this->valueResolver = $valueResolver;
        $this->staticTypeMapper = $staticTypeMapper;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Make implicit nullable param to explicit', [new CodeSample(<<<'CODE_SAMPLE'
function foo(string $param = null) {}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
function foo(?string $param = null) {}
CODE_SAMPLE
)]);
    }
    public function getNodeTypes() : array
    {
        return [Param::class];
    }
    /**
     * @param Param $node
     */
    public function refactor(Node $node) : ?Param
    {
        if (!$node->type instanceof Node) {
            return null;
        }
        if (!$node->default instanceof ConstFetch || !$this->valueResolver->isNull($node->default)) {
            return null;
        }
        $nodeType = $this->staticTypeMapper->mapPhpParserNodePHPStanType($node->type);
        if (TypeCombinator::containsNull($nodeType)) {
            return null;
        }
        // mixed can't be nullable, ref https://3v4l.org/YUkhH/rfc#vgit.master
        if ($nodeType instanceof MixedType) {
            return null;
        }
        $newNodeType = TypeCombinator::addNull($nodeType);
        $paramType = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($newNodeType, TypeKind::PARAM);
        // ensure it process valid Node, otherwise, just return null
        if (!$paramType instanceof Node) {
            return null;
        }
        // re-use existing node instead of reprint Node that may cause unnecessary FQCN
        if ($node->type instanceof UnionType) {
            $node->type->types[] = new Name('null');
        } elseif ($node->type instanceof ComplexType) {
            /** @var IntersectionType $nodeType */
            $nodeType = $node->type;
            $node->type = new UnionType([$nodeType, new Name('null')]);
        } else {
            $node->type = new NullableType($node->type);
        }
        return $node;
    }
    public function provideMinPhpVersion() : int
    {
        return PhpVersionFeature::DEPRECATE_IMPLICIT_NULLABLE_PARAM_TYPE;
    }
}
