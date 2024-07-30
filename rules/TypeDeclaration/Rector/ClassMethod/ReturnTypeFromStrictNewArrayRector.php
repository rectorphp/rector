<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Expr\Yield_;
use PhpParser\Node\Expr\YieldFrom;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Function_;
use PHPStan\Analyser\Scope;
use PHPStan\Type\ArrayType;
use PHPStan\Type\Constant\ConstantArrayType;
use PHPStan\Type\IntegerType;
use PHPStan\Type\MixedType;
use PHPStan\Type\NeverType;
use PHPStan\Type\Type;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTypeChanger;
use Rector\PhpParser\Node\BetterNodeFinder;
use Rector\Rector\AbstractScopeAwareRector;
use Rector\TypeDeclaration\NodeAnalyzer\ReturnAnalyzer;
use Rector\TypeDeclaration\TypeInferer\ReturnTypeInferer;
use Rector\ValueObject\PhpVersion;
use Rector\VendorLocker\NodeVendorLocker\ClassMethodReturnTypeOverrideGuard;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromStrictNewArrayRector\ReturnTypeFromStrictNewArrayRectorTest
 */
final class ReturnTypeFromStrictNewArrayRector extends AbstractScopeAwareRector implements MinPhpVersionInterface
{
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTypeChanger
     */
    private $phpDocTypeChanger;
    /**
     * @readonly
     * @var \Rector\VendorLocker\NodeVendorLocker\ClassMethodReturnTypeOverrideGuard
     */
    private $classMethodReturnTypeOverrideGuard;
    /**
     * @readonly
     * @var \Rector\TypeDeclaration\TypeInferer\ReturnTypeInferer
     */
    private $returnTypeInferer;
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory
     */
    private $phpDocInfoFactory;
    /**
     * @readonly
     * @var \Rector\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    /**
     * @readonly
     * @var \Rector\TypeDeclaration\NodeAnalyzer\ReturnAnalyzer
     */
    private $returnAnalyzer;
    public function __construct(PhpDocTypeChanger $phpDocTypeChanger, ClassMethodReturnTypeOverrideGuard $classMethodReturnTypeOverrideGuard, ReturnTypeInferer $returnTypeInferer, PhpDocInfoFactory $phpDocInfoFactory, BetterNodeFinder $betterNodeFinder, ReturnAnalyzer $returnAnalyzer)
    {
        $this->phpDocTypeChanger = $phpDocTypeChanger;
        $this->classMethodReturnTypeOverrideGuard = $classMethodReturnTypeOverrideGuard;
        $this->returnTypeInferer = $returnTypeInferer;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->betterNodeFinder = $betterNodeFinder;
        $this->returnAnalyzer = $returnAnalyzer;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Add strict return array type based on created empty array and returned', [new CodeSample(<<<'CODE_SAMPLE'
final class SomeClass
{
    public function run()
    {
        $values = [];

        return $values;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
final class SomeClass
{
    public function run(): array
    {
        $values = [];

        return $values;
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
    /**
     * @param ClassMethod|Function_ $node
     */
    public function refactorWithScope(Node $node, Scope $scope) : ?Node
    {
        if ($this->shouldSkip($node, $scope)) {
            return null;
        }
        // 1. is variable instantiated with array
        $stmts = $node->stmts;
        if ($stmts === null) {
            return null;
        }
        $variables = $this->matchArrayAssignedVariable($stmts);
        if ($variables === []) {
            return null;
        }
        // 2. skip yields
        if ($this->betterNodeFinder->hasInstancesOfInFunctionLikeScoped($node, [Yield_::class, YieldFrom::class])) {
            return null;
        }
        $returns = $this->betterNodeFinder->findReturnsScoped($node);
        if (!$this->returnAnalyzer->hasOnlyReturnWithExpr($node, $returns)) {
            return null;
        }
        $variables = $this->matchVariableNotOverriddenByNonArray($node, $variables);
        if ($variables === []) {
            return null;
        }
        if (\count($returns) > 1) {
            $returnType = $this->returnTypeInferer->inferFunctionLike($node);
            return $this->processAddArrayReturnType($node, $returnType);
        }
        $onlyReturn = $returns[0];
        if (!$onlyReturn->expr instanceof Variable) {
            return null;
        }
        if (!$this->nodeComparator->isNodeEqual($onlyReturn->expr, $variables)) {
            return null;
        }
        $returnType = $this->nodeTypeResolver->getNativeType($onlyReturn->expr);
        return $this->processAddArrayReturnType($node, $returnType);
    }
    public function provideMinPhpVersion() : int
    {
        return PhpVersion::PHP_70;
    }
    /**
     * @param \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_|\PhpParser\Node\Expr\Closure $node
     * @return \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_|\PhpParser\Node\Expr\Closure|null
     */
    private function processAddArrayReturnType($node, Type $returnType)
    {
        if (!$returnType->isArray()->yes()) {
            return null;
        }
        // always returns array
        $node->returnType = new Identifier('array');
        // add more precise array type if suitable
        if ($returnType instanceof ArrayType && $this->shouldAddReturnArrayDocType($returnType)) {
            $this->changeReturnType($node, $returnType);
        }
        return $node;
    }
    /**
     * @param \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_|\PhpParser\Node\Expr\Closure $node
     */
    private function shouldSkip($node, Scope $scope) : bool
    {
        if ($node->returnType instanceof Node) {
            return \true;
        }
        return $node instanceof ClassMethod && $this->classMethodReturnTypeOverrideGuard->shouldSkipClassMethod($node, $scope);
    }
    /**
     * @param \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_|\PhpParser\Node\Expr\Closure $node
     */
    private function changeReturnType($node, ArrayType $arrayType) : void
    {
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($node);
        // skip already filled type, on purpose
        if (!$phpDocInfo->getReturnType() instanceof MixedType) {
            return;
        }
        // can handle only exactly 1-type array
        if ($arrayType instanceof ConstantArrayType && \count($arrayType->getValueTypes()) !== 1) {
            return;
        }
        $narrowArrayType = new ArrayType(new MixedType(), $arrayType->getItemType());
        $this->phpDocTypeChanger->changeReturnType($node, $phpDocInfo, $narrowArrayType);
    }
    /**
     * @param Variable[] $variables
     * @return Variable[]
     * @param \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_ $functionLike
     */
    private function matchVariableNotOverriddenByNonArray($functionLike, array $variables) : array
    {
        // is variable overriden?
        /** @var Assign[] $assigns */
        $assigns = $this->betterNodeFinder->findInstancesOfInFunctionLikeScoped($functionLike, Assign::class);
        foreach ($assigns as $assign) {
            if (!$assign->var instanceof Variable) {
                continue;
            }
            foreach ($variables as $key => $variable) {
                if (!$this->nodeNameResolver->areNamesEqual($assign->var, $variable)) {
                    continue;
                }
                if ($assign->expr instanceof Array_) {
                    continue;
                }
                $nativeType = $this->nodeTypeResolver->getNativeType($assign->expr);
                if (!$nativeType->isArray()->yes()) {
                    unset($variables[$key]);
                }
            }
        }
        return $variables;
    }
    /**
     * @param Stmt[] $stmts
     * @return Variable[]
     */
    private function matchArrayAssignedVariable(array $stmts) : array
    {
        $variables = [];
        foreach ($stmts as $stmt) {
            if (!$stmt instanceof Expression) {
                continue;
            }
            if (!$stmt->expr instanceof Assign) {
                continue;
            }
            $assign = $stmt->expr;
            if (!$assign->var instanceof Variable) {
                continue;
            }
            $nativeType = $this->nodeTypeResolver->getNativeType($assign->expr);
            if ($nativeType->isArray()->yes()) {
                $variables[] = $assign->var;
            }
        }
        return $variables;
    }
    private function shouldAddReturnArrayDocType(ArrayType $arrayType) : bool
    {
        if ($arrayType instanceof ConstantArrayType) {
            if ($arrayType->getItemType() instanceof NeverType) {
                return \false;
            }
            // handle only simple arrays
            if (!$arrayType->getKeyType() instanceof IntegerType) {
                return \false;
            }
        }
        return \true;
    }
}
