<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Expr\Yield_;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\Return_;
use PHPStan\Type\ArrayType;
use PHPStan\Type\Constant\ConstantArrayType;
use PHPStan\Type\MixedType;
use PHPStan\Type\NeverType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use PHPStan\Type\VerbosityLevel;
use Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTypeChanger;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\PhpVersion;
use Rector\NodeTypeResolver\TypeComparator\TypeComparator;
use Rector\VendorLocker\NodeVendorLocker\ClassMethodReturnTypeOverrideGuard;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromStrictNewArrayRector\ReturnTypeFromStrictNewArrayRectorTest
 */
final class ReturnTypeFromStrictNewArrayRector extends AbstractRector implements MinPhpVersionInterface
{
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTypeChanger
     */
    private $phpDocTypeChanger;
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\TypeComparator\TypeComparator
     */
    private $typeComparator;
    /**
     * @readonly
     * @var \Rector\VendorLocker\NodeVendorLocker\ClassMethodReturnTypeOverrideGuard
     */
    private $classMethodReturnTypeOverrideGuard;
    public function __construct(PhpDocTypeChanger $phpDocTypeChanger, TypeComparator $typeComparator, ClassMethodReturnTypeOverrideGuard $classMethodReturnTypeOverrideGuard)
    {
        $this->phpDocTypeChanger = $phpDocTypeChanger;
        $this->typeComparator = $typeComparator;
        $this->classMethodReturnTypeOverrideGuard = $classMethodReturnTypeOverrideGuard;
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
        return [ClassMethod::class, Function_::class, Closure::class];
    }
    /**
     * @param ClassMethod|Function_|Closure $node
     */
    public function refactor(Node $node) : ?Node
    {
        if ($this->shouldSkip($node)) {
            return null;
        }
        // 1. is variable instantiated with array
        $stmts = $node->stmts;
        if ($stmts === null) {
            return null;
        }
        $variable = $this->matchArrayAssignedVariable($stmts);
        if (!$variable instanceof Variable) {
            return null;
        }
        // 2. skip yields
        if ($this->betterNodeFinder->hasInstancesOfInFunctionLikeScoped($node, [Yield_::class])) {
            return null;
        }
        /** @var Return_[] $returns */
        $returns = $this->betterNodeFinder->findInstancesOfInFunctionLikeScoped($node, Return_::class);
        if (\count($returns) !== 1) {
            return null;
        }
        if ($this->isVariableOverriddenWithNonArray($node, $variable)) {
            return null;
        }
        $onlyReturn = $returns[0];
        if (!$onlyReturn->expr instanceof Variable) {
            return null;
        }
        $returnType = $this->nodeTypeResolver->getType($onlyReturn->expr);
        if (!$returnType->isArray()->yes()) {
            return null;
        }
        if (!$this->nodeNameResolver->areNamesEqual($onlyReturn->expr, $variable)) {
            return null;
        }
        // 3. always returns array
        $node->returnType = new Identifier('array');
        // 4. add more precise type if suitable
        $exprType = $this->getType($onlyReturn->expr);
        if ($this->shouldAddReturnArrayDocType($exprType)) {
            $this->changeReturnType($node, $exprType);
        }
        return $node;
    }
    public function provideMinPhpVersion() : int
    {
        return PhpVersion::PHP_70;
    }
    /**
     * @param \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_|\PhpParser\Node\Expr\Closure $node
     */
    private function shouldSkip($node) : bool
    {
        if ($node->returnType !== null) {
            return \true;
        }
        return $node instanceof ClassMethod && $this->classMethodReturnTypeOverrideGuard->shouldSkipClassMethod($node);
    }
    private function changeReturnType(Node $node, Type $exprType) : void
    {
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($node);
        $exprType = $this->narrowConstantArrayType($exprType);
        if (!$this->typeComparator->isSubtype($phpDocInfo->getReturnType(), $exprType)) {
            $this->phpDocTypeChanger->changeReturnType($phpDocInfo, $exprType);
        }
    }
    /**
     * @param \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_|\PhpParser\Node\Expr\Closure $functionLike
     */
    private function isVariableOverriddenWithNonArray($functionLike, Variable $variable) : bool
    {
        // is variable overriden?
        /** @var Assign[] $assigns */
        $assigns = $this->betterNodeFinder->findInstancesOfInFunctionLikeScoped($functionLike, Assign::class);
        foreach ($assigns as $assign) {
            if (!$assign->var instanceof Variable) {
                continue;
            }
            if (!$this->nodeNameResolver->areNamesEqual($assign->var, $variable)) {
                continue;
            }
            if (!$assign->expr instanceof Array_) {
                return \true;
            }
        }
        return \false;
    }
    /**
     * @param Stmt[] $stmts
     */
    private function matchArrayAssignedVariable(array $stmts) : ?\PhpParser\Node\Expr\Variable
    {
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
            if (!$assign->expr instanceof Array_) {
                continue;
            }
            return $assign->var;
        }
        return null;
    }
    private function shouldAddReturnArrayDocType(Type $exprType) : bool
    {
        if ($exprType instanceof ConstantArrayType) {
            // sign of empty array, keep empty
            return !$exprType->getItemType() instanceof NeverType;
        }
        return $exprType->isArray()->yes();
    }
    private function narrowConstantArrayType(Type $type) : Type
    {
        if (!$type instanceof ConstantArrayType) {
            return $type;
        }
        if (\count($type->getValueTypes()) === 1) {
            $singleValueType = $type->getValueTypes()[0];
            if ($singleValueType instanceof ObjectType) {
                return $type;
            }
        }
        $printedDescription = $type->describe(VerbosityLevel::precise());
        if (\strlen($printedDescription) > 50) {
            return new ArrayType(new MixedType(), new MixedType());
        }
        return $type;
    }
}
