<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Stmt\Catch_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Finally_;
use PhpParser\Node\Stmt\Return_;
use PhpParser\Node\Stmt\TryCatch;
use PHPStan\Type\Type;
use Rector\NodeTypeResolver\TypeComparator\TypeComparator;
use Rector\PhpParser\Node\BetterNodeFinder;
use Rector\PHPStan\ScopeFetcher;
use Rector\PHPStanStaticTypeMapper\Enum\TypeKind;
use Rector\Rector\AbstractRector;
use Rector\StaticTypeMapper\StaticTypeMapper;
use Rector\TypeDeclaration\NodeAnalyzer\ReturnAnalyzer;
use Rector\VendorLocker\NodeVendorLocker\ClassMethodReturnTypeOverrideGuard;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\TypeDeclaration\Rector\ClassMethod\AddReturnTypeFromTryCatchTypeRector\AddReturnTypeFromTryCatchTypeRectorTest
 */
final class AddReturnTypeFromTryCatchTypeRector extends AbstractRector
{
    /**
     * @readonly
     */
    private TypeComparator $typeComparator;
    /**
     * @readonly
     */
    private StaticTypeMapper $staticTypeMapper;
    /**
     * @readonly
     */
    private ClassMethodReturnTypeOverrideGuard $classMethodReturnTypeOverrideGuard;
    /**
     * @readonly
     */
    private ReturnAnalyzer $returnAnalyzer;
    /**
     * @readonly
     */
    private BetterNodeFinder $betterNodeFinder;
    public function __construct(TypeComparator $typeComparator, StaticTypeMapper $staticTypeMapper, ClassMethodReturnTypeOverrideGuard $classMethodReturnTypeOverrideGuard, ReturnAnalyzer $returnAnalyzer, BetterNodeFinder $betterNodeFinder)
    {
        $this->typeComparator = $typeComparator;
        $this->staticTypeMapper = $staticTypeMapper;
        $this->classMethodReturnTypeOverrideGuard = $classMethodReturnTypeOverrideGuard;
        $this->returnAnalyzer = $returnAnalyzer;
        $this->betterNodeFinder = $betterNodeFinder;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Add known type declarations based on first-level try/catch return values', [new CodeSample(<<<'CODE_SAMPLE'
final class SomeClass
{
    public function run()
    {
        try {
            return 1;
        } catch (\Exception $e) {
            return 2;
        }
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
final class SomeClass
{
    public function run(): int
    {
        try {
            return 1;
        } catch (\Exception $e) {
            return 2;
        }
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
        return [ClassMethod::class];
    }
    /**
     * @param ClassMethod $node
     */
    public function refactor(Node $node): ?Node
    {
        $scope = ScopeFetcher::fetch($node);
        if ($this->classMethodReturnTypeOverrideGuard->shouldSkipClassMethod($node, $scope)) {
            return null;
        }
        // already known type
        if ($node->returnType instanceof Node) {
            return null;
        }
        $tryReturnType = null;
        $catchReturnTypes = [];
        $returns = $this->betterNodeFinder->findReturnsScoped($node);
        if (!$this->returnAnalyzer->hasOnlyReturnWithExpr($node, $returns)) {
            return null;
        }
        foreach ((array) $node->stmts as $classMethodStmt) {
            if (!$classMethodStmt instanceof TryCatch) {
                continue;
            }
            // skip if there is no catch
            if ($classMethodStmt->catches === []) {
                continue;
            }
            $tryCatch = $classMethodStmt;
            $tryReturnType = $this->matchReturnType($tryCatch);
            foreach ($tryCatch->catches as $catch) {
                $currentCatchType = $this->matchReturnType($catch);
                // each catch must have type
                if (!$currentCatchType instanceof Type) {
                    return null;
                }
                $catchReturnTypes[] = $currentCatchType;
            }
            if ($tryCatch->finally instanceof Finally_) {
                $finallyReturnType = $this->matchReturnType($tryCatch->finally);
                if ($finallyReturnType instanceof Type) {
                    $catchReturnTypes[] = $finallyReturnType;
                }
            }
        }
        if (!$tryReturnType instanceof Type) {
            return null;
        }
        foreach ($catchReturnTypes as $catchReturnType) {
            if (!$this->typeComparator->areTypesEqual($catchReturnType, $tryReturnType)) {
                return null;
            }
        }
        $returnType = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($tryReturnType, TypeKind::RETURN);
        if (!$returnType instanceof Node) {
            return null;
        }
        $node->returnType = $returnType;
        return $node;
    }
    /**
     * @param \PhpParser\Node\Stmt\TryCatch|\PhpParser\Node\Stmt\Catch_|\PhpParser\Node\Stmt\Finally_ $tryOrCatchOrFinally
     */
    private function matchReturnType($tryOrCatchOrFinally): ?Type
    {
        foreach ($tryOrCatchOrFinally->stmts as $stmt) {
            if (!$stmt instanceof Return_) {
                continue;
            }
            if (!$stmt->expr instanceof Expr) {
                continue;
            }
            return $this->nodeTypeResolver->getNativeType($stmt->expr);
        }
        return null;
    }
}
