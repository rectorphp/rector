<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\NullableType;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PHPStan\Type\UnionType;
use Rector\PHPStan\ScopeFetcher;
use Rector\PHPStanStaticTypeMapper\Enum\TypeKind;
use Rector\PHPStanStaticTypeMapper\TypeMapper\UnionTypeMapper;
use Rector\Rector\AbstractRector;
use Rector\TypeDeclaration\TypeInferer\ReturnTypeInferer;
use Rector\ValueObject\PhpVersionFeature;
use Rector\VendorLocker\NodeVendorLocker\ClassMethodReturnTypeOverrideGuard;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\TypeDeclaration\Rector\ClassMethod\ReturnNullableTypeRector\ReturnNullableTypeRectorTest
 */
final class ReturnNullableTypeRector extends AbstractRector implements MinPhpVersionInterface
{
    /**
     * @readonly
     */
    private UnionTypeMapper $unionTypeMapper;
    /**
     * @readonly
     */
    private ClassMethodReturnTypeOverrideGuard $classMethodReturnTypeOverrideGuard;
    /**
     * @readonly
     */
    private ReturnTypeInferer $returnTypeInferer;
    public function __construct(UnionTypeMapper $unionTypeMapper, ClassMethodReturnTypeOverrideGuard $classMethodReturnTypeOverrideGuard, ReturnTypeInferer $returnTypeInferer)
    {
        $this->unionTypeMapper = $unionTypeMapper;
        $this->classMethodReturnTypeOverrideGuard = $classMethodReturnTypeOverrideGuard;
        $this->returnTypeInferer = $returnTypeInferer;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Add basic ? nullable type to class methods and functions, as of PHP 7.1', [new CodeSample(<<<'CODE_SAMPLE'
final class SomeClass
{
    public function getData()
    {
        if (rand(0, 1)) {
            return null;
        }

        return 100;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
final class SomeClass
{
    public function getData(): ?int
    {
        if (rand(0, 1)) {
            return null;
        }

        return 100;
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
        return [ClassMethod::class, Function_::class];
    }
    public function provideMinPhpVersion() : int
    {
        return PhpVersionFeature::NULLABLE_TYPE;
    }
    /**
     * @param ClassMethod|Function_ $node
     */
    public function refactor(Node $node) : ?Node
    {
        $scope = ScopeFetcher::fetch($node);
        // empty body, nothing to resolve
        if ($node->stmts === null || $node->stmts === []) {
            return null;
        }
        // type is already known, skip
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
        // handled by union PHP 8.0 rule
        if (!$returnType instanceof NullableType) {
            return null;
        }
        $node->returnType = $returnType;
        return $node;
    }
}
