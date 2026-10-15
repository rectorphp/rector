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
use Rector\Rector\AbstractRector;
use Rector\RuleDoc\CodeSample\CodeSample;
use Rector\RuleDoc\RuleDefinition;
use Rector\TypeDeclaration\TypeInferer\ReturnTypeInferer;
use Rector\TypeDeclaration\TypeInferer\SilentVoidResolver;
use Rector\ValueObject\MethodName;
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
    public function __construct(SilentVoidResolver $silentVoidResolver, PhpDocInfoFactory $phpDocInfoFactory, TypeFactory $typeFactory, PhpDocTypeChanger $phpDocTypeChanger, ReturnTypeInferer $returnTypeInferer)
    {
        $this->silentVoidResolver = $silentVoidResolver;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->typeFactory = $typeFactory;
        $this->phpDocTypeChanger = $phpDocTypeChanger;
        $this->returnTypeInferer = $returnTypeInferer;
    }
    public function getRuleDefinition(): RuleDefinition
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
    public function getNodeTypes(): array
    {
        return [ClassMethod::class, Function_::class];
    }
    /**
     * @param ClassMethod|Function_ $node
     */
    public function refactor(Node $node): ?Node
    {
        // known return type, nothing to improve
        if ($node->returnType instanceof Node) {
            return null;
        }
        // bodyless method (abstract/interface), nothing to improve
        if ($node->stmts === null) {
            return null;
        }
        if ($node instanceof ClassMethod && $this->isName($node, MethodName::CONSTRUCT)) {
            return null;
        }
        $hasGoto = \false;
        $hasValueReturn = \false;
        $bareReturns = [];
        $this->traverseNodesWithCallable($node->stmts, static function (Node $subNode) use (&$hasGoto, &$hasValueReturn, &$bareReturns): ?int {
            if ($subNode instanceof Class_ || $subNode instanceof Function_ || $subNode instanceof Closure) {
                return NodeVisitor::DONT_TRAVERSE_CURRENT_AND_CHILDREN;
            }
            if ($subNode instanceof Goto_) {
                $hasGoto = \true;
                return null;
            }
            if ($subNode instanceof Return_) {
                if ($subNode->expr instanceof Expr) {
                    $hasValueReturn = \true;
                } else {
                    $bareReturns[] = $subNode;
                }
            }
            return null;
        });
        // goto makes control flow analysis unreliable
        if ($hasGoto) {
            return null;
        }
        // no returned value means the inferred type can never be a value|void union
        if (!$hasValueReturn) {
            return null;
        }
        $returnType = $this->returnTypeInferer->inferFunctionLike($node);
        if (!$returnType instanceof UnionType) {
            return null;
        }
        $hasChanged = \false;
        foreach ($bareReturns as $bareReturn) {
            $bareReturn->expr = new ConstFetch(new Name('null'));
            $hasChanged = \true;
        }
        // allow non native @return never type use
        // to avoid noise for addition returns
        if (!$this->silentVoidResolver->hasSilentVoid($node, \false)) {
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
    private function transformDocUnionVoidToUnionNull($node): void
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
