<?php

declare (strict_types=1);
namespace Rector\PHPUnit\CodeQuality\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use Rector\PhpParser\Node\BetterNodeFinder;
use Rector\PHPStanStaticTypeMapper\Enum\TypeKind;
use Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer;
use Rector\Rector\AbstractRector;
use Rector\StaticTypeMapper\StaticTypeMapper;
use Rector\TypeDeclaration\NodeAnalyzer\ReturnAnalyzer;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\PHPUnit\Tests\CodeQuality\Rector\Class_\AddReturnTypeToDependedRector\AddReturnTypeToDependedRectorTest
 */
final class AddReturnTypeToDependedRector extends AbstractRector
{
    /**
     * @readonly
     */
    private TestsNodeAnalyzer $testsNodeAnalyzer;
    /**
     * @readonly
     */
    private ReturnAnalyzer $returnAnalyzer;
    /**
     * @readonly
     */
    private StaticTypeMapper $staticTypeMapper;
    /**
     * @readonly
     */
    private BetterNodeFinder $betterNodeFinder;
    public function __construct(TestsNodeAnalyzer $testsNodeAnalyzer, ReturnAnalyzer $returnAnalyzer, StaticTypeMapper $staticTypeMapper, BetterNodeFinder $betterNodeFinder)
    {
        $this->testsNodeAnalyzer = $testsNodeAnalyzer;
        $this->returnAnalyzer = $returnAnalyzer;
        $this->staticTypeMapper = $staticTypeMapper;
        $this->betterNodeFinder = $betterNodeFinder;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Add return type declaration to a test method that returns type', [new CodeSample(<<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;

final class SomeTest extends TestCase
{
    public function test()
    {
        $value = new \stdClass();

        return $value;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;

final class SomeTest extends TestCase
{
    public function test(): \stdClass
    {
        $value = new \stdClass();

        return $value;
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [Class_::class];
    }
    /**
     * @param Class_ $node
     */
    public function refactor(Node $node): ?Node
    {
        if (!$this->testsNodeAnalyzer->isInTestClass($node)) {
            return null;
        }
        $hasChanged = \false;
        foreach ($node->getMethods() as $classMethod) {
            if (!$classMethod->isPublic()) {
                continue;
            }
            if (!$this->testsNodeAnalyzer->isTestClassMethod($classMethod)) {
                continue;
            }
            // already known return type
            if ($classMethod->returnType instanceof Node) {
                continue;
            }
            $returns = $this->betterNodeFinder->findReturnsScoped($classMethod);
            if (!$this->returnAnalyzer->hasOnlyReturnWithExpr($classMethod, $returns)) {
                return null;
            }
            if (count($returns) !== 1) {
                continue;
            }
            $soleReturnExpr = $returns[0]->expr;
            if ($soleReturnExpr === null) {
                continue;
            }
            // does return a type?
            $returnedExprType = $this->nodeTypeResolver->getNativeType($soleReturnExpr);
            $returnType = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($returnedExprType, TypeKind::RETURN);
            if (!$returnType instanceof Node) {
                continue;
            }
            $classMethod->returnType = $returnType;
            $hasChanged = \true;
        }
        if ($hasChanged === \false) {
            return null;
        }
        return $node;
    }
}
