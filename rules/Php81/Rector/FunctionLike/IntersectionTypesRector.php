<?php

declare (strict_types=1);
namespace Rector\Php81\Rector\FunctionLike;

use PhpParser\Node;
use PhpParser\Node\Expr\ArrowFunction;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PHPStan\Type\IntersectionType;
use PHPStan\Type\TypeWithClassName;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\PHPStanStaticTypeMapper\Enum\TypeKind;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\Php81\Rector\FunctionLike\IntersectionTypesRector\IntersectionTypesRectorTest
 */
final class IntersectionTypesRector extends \Rector\Core\Rector\AbstractRector implements \Rector\VersionBonding\Contract\MinPhpVersionInterface
{
    /**
     * @var bool
     */
    private $hasChanged = \false;
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Change docs to intersection types, where possible (properties are covered by TypedPropertyRector (@todo))', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
final class SomeClass
{
    /**
     * @param Foo&Bar $types
     */
    public function process($types)
    {
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
final class SomeClass
{
    public function process(Foo&Bar $types)
    {
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Expr\ArrowFunction::class, \PhpParser\Node\Expr\Closure::class, \PhpParser\Node\Stmt\ClassMethod::class, \PhpParser\Node\Stmt\Function_::class];
    }
    /**
     * @param ArrowFunction|Closure|ClassMethod|Function_ $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        $this->hasChanged = \false;
        $phpDocInfo = $this->phpDocInfoFactory->createFromNode($node);
        if (!$phpDocInfo instanceof \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo) {
            return null;
        }
        $this->refactorParamTypes($node, $phpDocInfo);
        // $this->refactorReturnType($node, $phpDocInfo);
        if ($this->hasChanged) {
            return $node;
        }
        return null;
    }
    public function provideMinPhpVersion() : int
    {
        return \Rector\Core\ValueObject\PhpVersionFeature::INTERSECTION_TYPES;
    }
    /**
     * @param \PhpParser\Node\Expr\ArrowFunction|\PhpParser\Node\Expr\Closure|\PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_ $functionLike
     */
    private function refactorParamTypes($functionLike, \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo $phpDocInfo) : void
    {
        foreach ($functionLike->params as $param) {
            if ($param->type !== null) {
                continue;
            }
            /** @var string $paramName */
            $paramName = $this->getName($param->var);
            $paramType = $phpDocInfo->getParamType($paramName);
            if (!$paramType instanceof \PHPStan\Type\IntersectionType) {
                continue;
            }
            if (!$this->isIntersectionableType($paramType)) {
                continue;
            }
            $phpParserIntersectionType = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($paramType, \Rector\PHPStanStaticTypeMapper\Enum\TypeKind::PARAM());
            if (!$phpParserIntersectionType instanceof \PhpParser\Node\IntersectionType) {
                continue;
            }
            $param->type = $phpParserIntersectionType;
            $this->hasChanged = \true;
        }
    }
    /**
     * Only class-type are supported https://wiki.php.net/rfc/pure-intersection-types#supported_types
     */
    private function isIntersectionableType(\PHPStan\Type\IntersectionType $intersectionType) : bool
    {
        foreach ($intersectionType->getTypes() as $intersectionedType) {
            if ($intersectionedType instanceof \PHPStan\Type\TypeWithClassName) {
                continue;
            }
            return \false;
        }
        return \true;
    }
}
