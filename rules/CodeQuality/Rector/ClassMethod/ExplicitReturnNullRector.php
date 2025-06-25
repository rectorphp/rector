<?php

declare (strict_types=1);
namespace Rector\CodeQuality\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\Goto_;
use PhpParser\Node\Stmt\Return_;
use PhpParser\NodeVisitor;
use PHPStan\Type\NullType;
use PHPStan\Type\UnionType;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTypeChanger;
use Rector\NodeTypeResolver\PHPStan\Type\TypeFactory;
use Rector\PhpParser\Node\BetterNodeFinder;
use Rector\Rector\AbstractRector;
use Rector\TypeDeclaration\TypeInferer\ReturnTypeInferer;
use Rector\TypeDeclaration\TypeInferer\SilentVoidResolver;
use Rector\ValueObject\MethodName;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\CodeQuality\Rector\ClassMethod\ExplicitReturnNullRector\ExplicitReturnNullRectorTest
 */
final class ExplicitReturnNullRector extends AbstractRector
{
    /**
     * @readonly
     */
    private SilentVoidResolver $silentVoidResolver;
    /**
     * @readonly
     */
    private PhpDocInfoFactory $phpDocInfoFactory;
    /**
     * @readonly
     */
    private TypeFactory $typeFactory;
    /**
     * @readonly
     */
    private PhpDocTypeChanger $phpDocTypeChanger;
    /**
     * @readonly
     */
    private ReturnTypeInferer $returnTypeInferer;
    /**
     * @readonly
     */
    private BetterNodeFinder $betterNodeFinder;
    public function __construct(SilentVoidResolver $silentVoidResolver, PhpDocInfoFactory $phpDocInfoFactory, TypeFactory $typeFactory, PhpDocTypeChanger $phpDocTypeChanger, ReturnTypeInferer $returnTypeInferer, BetterNodeFinder $betterNodeFinder)
    {
        $this->silentVoidResolver = $silentVoidResolver;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->typeFactory = $typeFactory;
        $this->phpDocTypeChanger = $phpDocTypeChanger;
        $this->returnTypeInferer = $returnTypeInferer;
        $this->betterNodeFinder = $betterNodeFinder;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Add explicit return null to method/function that returns a value, but missed main return', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    /**
     * @return string|void
     */
    public function run(int $number)
    {
        if ($number > 50) {
            return 'yes';
        }
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    /**
     * @return string|null
     */
    public function run(int $number)
    {
        if ($number > 50) {
            return 'yes';
        }

        return null;
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
    public function refactor(Node $node) : ?Node
    {
        // known return type, nothing to improve
        if ($node->returnType instanceof Node) {
            return null;
        }
        if ($node instanceof ClassMethod && $this->isName($node, MethodName::CONSTRUCT)) {
            return null;
        }
        $returnType = $this->returnTypeInferer->inferFunctionLike($node);
        if (!$returnType instanceof UnionType) {
            return null;
        }
        $hasGoto = (bool) $this->betterNodeFinder->findFirstInFunctionLikeScoped($node, fn(Node $node): bool => $node instanceof Goto_);
        if ($hasGoto) {
            return null;
        }
        $hasChanged = \false;
        $this->traverseNodesWithCallable((array) $node->stmts, static function (Node $node) use(&$hasChanged) {
            if ($node instanceof Class_ || $node instanceof Function_ || $node instanceof Closure) {
                return NodeVisitor::DONT_TRAVERSE_CURRENT_AND_CHILDREN;
            }
            if ($node instanceof Return_ && !$node->expr instanceof Expr) {
                $hasChanged = \true;
                $node->expr = new ConstFetch(new Name('null'));
                return $node;
            }
            return null;
        });
        if (!$this->silentVoidResolver->hasSilentVoid($node)) {
            if ($hasChanged) {
                $this->transformDocUnionVoidToUnionNull($node);
                return $node;
            }
            return null;
        }
        $node->stmts[] = new Return_(new ConstFetch(new Name('null')));
        $this->transformDocUnionVoidToUnionNull($node);
        return $node;
    }
    /**
     * @param \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_ $node
     */
    private function transformDocUnionVoidToUnionNull($node) : void
    {
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($node);
        $returnType = $phpDocInfo->getReturnType();
        if (!$returnType instanceof UnionType) {
            return;
        }
        $newTypes = [];
        $hasChanged = \false;
        foreach ($returnType->getTypes() as $type) {
            if ($type->isVoid()->yes()) {
                $type = new NullType();
                $hasChanged = \true;
            }
            $newTypes[] = $type;
        }
        if (!$hasChanged) {
            return;
        }
        $type = $this->typeFactory->createMixedPassedOrUnionTypeAndKeepConstant($newTypes);
        if (!$type instanceof UnionType) {
            return;
        }
        $this->phpDocTypeChanger->changeReturnType($node, $phpDocInfo, $type);
    }
}
