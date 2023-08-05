<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PHPStan\Analyser\Scope;
use PHPStan\Type\UnionType;
use Rector\Core\Rector\AbstractScopeAwareRector;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\PHPStanStaticTypeMapper\Enum\TypeKind;
use Rector\PHPStanStaticTypeMapper\TypeMapper\UnionTypeMapper;
use Rector\TypeDeclaration\TypeInferer\ReturnTypeInferer;
use Rector\VendorLocker\NodeVendorLocker\ClassMethodReturnTypeOverrideGuard;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\TypeDeclaration\Rector\ClassMethod\ReturnUnionTypeRector\ReturnUnionTypeRectorTest
 */
final class ReturnUnionTypeRector extends AbstractScopeAwareRector implements MinPhpVersionInterface
{
    /**
     * @readonly
     * @var \Rector\TypeDeclaration\TypeInferer\ReturnTypeInferer
     */
    private $returnTypeInferer;
    /**
     * @readonly
     * @var \Rector\VendorLocker\NodeVendorLocker\ClassMethodReturnTypeOverrideGuard
     */
    private $classMethodReturnTypeOverrideGuard;
    /**
     * @readonly
     * @var \Rector\PHPStanStaticTypeMapper\TypeMapper\UnionTypeMapper
     */
    private $unionTypeMapper;
    public function __construct(ReturnTypeInferer $returnTypeInferer, ClassMethodReturnTypeOverrideGuard $classMethodReturnTypeOverrideGuard, UnionTypeMapper $unionTypeMapper)
    {
        $this->returnTypeInferer = $returnTypeInferer;
        $this->classMethodReturnTypeOverrideGuard = $classMethodReturnTypeOverrideGuard;
        $this->unionTypeMapper = $unionTypeMapper;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Add union return type', [new CodeSample(<<<'CODE_SAMPLE'
final class SomeClass
{
    public function getData()
    {
        if (rand(0, 1)) {
            return null;
        }

        if (rand(0, 1)) {
            return new DateTime('now');
        }

        return new stdClass;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
final class SomeClass
{
    public function getData(): null|\DateTime|\stdClass
    {
        if (rand(0, 1)) {
            return null;
        }

        if (rand(0, 1)) {
            return new DateTime('now');
        }

        return new stdClass;
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
        return [ClassMethod::class, Function_::class, Closure::class];
    }
    public function provideMinPhpVersion() : int
    {
        return PhpVersionFeature::NULLABLE_TYPE;
    }
    /**
     * @param ClassMethod|Function_|Closure $node
     */
    public function refactorWithScope(Node $node, Scope $scope) : ?Node
    {
        if ($node->stmts === null) {
            return null;
        }
        if ($node->returnType instanceof Node) {
            return null;
        }
        if ($node instanceof ClassMethod && $this->classMethodReturnTypeOverrideGuard->shouldSkipClassMethod($node, $scope)) {
            return null;
        }
        $inferReturnType = $this->returnTypeInferer->inferFunctionLike($node);
        if (!$inferReturnType instanceof UnionType) {
            return null;
        }
        $returnType = $this->unionTypeMapper->mapToPhpParserNode($inferReturnType, TypeKind::RETURN);
        if (!$returnType instanceof Node) {
            return null;
        }
        $node->returnType = $returnType;
        return $node;
    }
}
