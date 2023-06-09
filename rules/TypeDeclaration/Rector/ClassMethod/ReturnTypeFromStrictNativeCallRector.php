<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PHPStan\Analyser\Scope;
use PHPStan\Type\MixedType;
use Rector\Core\Rector\AbstractScopeAwareRector;
use Rector\Core\ValueObject\PhpVersion;
use Rector\NodeTypeResolver\PHPStan\Type\TypeFactory;
use Rector\PHPStanStaticTypeMapper\Enum\TypeKind;
use Rector\TypeDeclaration\NodeAnalyzer\ReturnTypeAnalyzer\StrictNativeFunctionReturnTypeAnalyzer;
use Rector\VendorLocker\NodeVendorLocker\ClassMethodReturnTypeOverrideGuard;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromStrictNativeCallRector\ReturnTypeFromStrictNativeCallRectorTest
 */
final class ReturnTypeFromStrictNativeCallRector extends AbstractScopeAwareRector implements MinPhpVersionInterface
{
    /**
     * @readonly
     * @var \Rector\TypeDeclaration\NodeAnalyzer\ReturnTypeAnalyzer\StrictNativeFunctionReturnTypeAnalyzer
     */
    private $strictNativeFunctionReturnTypeAnalyzer;
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\PHPStan\Type\TypeFactory
     */
    private $typeFactory;
    /**
     * @readonly
     * @var \Rector\VendorLocker\NodeVendorLocker\ClassMethodReturnTypeOverrideGuard
     */
    private $classMethodReturnTypeOverrideGuard;
    public function __construct(StrictNativeFunctionReturnTypeAnalyzer $strictNativeFunctionReturnTypeAnalyzer, TypeFactory $typeFactory, ClassMethodReturnTypeOverrideGuard $classMethodReturnTypeOverrideGuard)
    {
        $this->strictNativeFunctionReturnTypeAnalyzer = $strictNativeFunctionReturnTypeAnalyzer;
        $this->typeFactory = $typeFactory;
        $this->classMethodReturnTypeOverrideGuard = $classMethodReturnTypeOverrideGuard;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Add strict return type based native function or class method return', [new CodeSample(<<<'CODE_SAMPLE'
final class SomeClass
{
    public function run()
    {
        return strlen('value');
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
final class SomeClass
{
    public function run(): int
    {
        return strlen('value');
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
        return [ClassMethod::class, Closure::class, Function_::class];
    }
    /**
     * @param ClassMethod|Closure|Function_ $node
     */
    public function refactorWithScope(Node $node, Scope $scope) : ?Node
    {
        if ($node->returnType !== null) {
            return null;
        }
        if ($node instanceof ClassMethod && $this->classMethodReturnTypeOverrideGuard->shouldSkipClassMethod($node, $scope)) {
            return null;
        }
        $nativeCallLikes = $this->strictNativeFunctionReturnTypeAnalyzer->matchAlwaysReturnNativeCallLikes($node);
        if ($nativeCallLikes === null) {
            return null;
        }
        $callLikeTypes = [];
        foreach ($nativeCallLikes as $nativeCallLike) {
            $callLikeTypes[] = $this->getType($nativeCallLike);
        }
        $returnType = $this->typeFactory->createMixedPassedOrUnionType($callLikeTypes);
        if ($returnType instanceof MixedType) {
            return null;
        }
        $returnTypeNode = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($returnType, TypeKind::RETURN);
        if (!$returnTypeNode instanceof Node) {
            return null;
        }
        $node->returnType = $returnTypeNode;
        return $node;
    }
    public function provideMinPhpVersion() : int
    {
        return PhpVersion::PHP_70;
    }
}
