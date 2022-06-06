<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\DowngradePhp81\Rector\FunctionLike;

use RectorPrefix20220606\PhpParser\Node;
use RectorPrefix20220606\PhpParser\Node\ComplexType;
use RectorPrefix20220606\PhpParser\Node\Expr;
use RectorPrefix20220606\PhpParser\Node\Expr\ArrowFunction;
use RectorPrefix20220606\PhpParser\Node\Expr\Assign;
use RectorPrefix20220606\PhpParser\Node\Expr\AssignOp\Coalesce as AssignCoalesce;
use RectorPrefix20220606\PhpParser\Node\Expr\BinaryOp\Coalesce;
use RectorPrefix20220606\PhpParser\Node\Expr\Closure;
use RectorPrefix20220606\PhpParser\Node\Expr\New_;
use RectorPrefix20220606\PhpParser\Node\Expr\PropertyFetch;
use RectorPrefix20220606\PhpParser\Node\Expr\Variable;
use RectorPrefix20220606\PhpParser\Node\FunctionLike;
use RectorPrefix20220606\PhpParser\Node\Identifier;
use RectorPrefix20220606\PhpParser\Node\IntersectionType;
use RectorPrefix20220606\PhpParser\Node\Name;
use RectorPrefix20220606\PhpParser\Node\NullableType;
use RectorPrefix20220606\PhpParser\Node\Param;
use RectorPrefix20220606\PhpParser\Node\Stmt\ClassMethod;
use RectorPrefix20220606\PhpParser\Node\Stmt\Expression;
use RectorPrefix20220606\PhpParser\Node\Stmt\Function_;
use RectorPrefix20220606\PhpParser\Node\Stmt\Return_;
use RectorPrefix20220606\PhpParser\Node\UnionType;
use RectorPrefix20220606\Rector\Core\Exception\ShouldNotHappenException;
use RectorPrefix20220606\Rector\Core\Rector\AbstractRector;
use RectorPrefix20220606\Rector\Core\ValueObject\MethodName;
use RectorPrefix20220606\Rector\Php72\NodeFactory\AnonymousFunctionFactory;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://wiki.php.net/rfc/new_in_initializers
 *
 * @see \Rector\Tests\DowngradePhp81\Rector\FunctionLike\DowngradeNewInInitializerRector\DowngradeNewInInitializerRectorTest
 */
final class DowngradeNewInInitializerRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\Php72\NodeFactory\AnonymousFunctionFactory
     */
    private $anonymousFunctionFactory;
    public function __construct(AnonymousFunctionFactory $anonymousFunctionFactory)
    {
        $this->anonymousFunctionFactory = $anonymousFunctionFactory;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Replace New in initializers', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function __construct(
        private Logger $logger = new NullLogger,
    ) {
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function __construct(
        private ?Logger $logger = null,
    ) {
        $this->logger = $logger ?? new NullLogger;
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
        return [FunctionLike::class];
    }
    /**
     * @param FunctionLike $node
     */
    public function refactor(Node $node) : ?FunctionLike
    {
        if ($this->shouldSkip($node)) {
            return null;
        }
        /** @var ClassMethod|Closure|Function_ $node */
        $node = $this->convertArrowFunctionToClosure($node);
        return $this->replaceNewInParams($node);
    }
    private function shouldSkip(FunctionLike $functionLike) : bool
    {
        foreach ($functionLike->getParams() as $param) {
            if ($this->isParamSkipped($param)) {
                continue;
            }
            return \false;
        }
        return \true;
    }
    private function convertArrowFunctionToClosure(FunctionLike $functionLike) : FunctionLike
    {
        if (!$functionLike instanceof ArrowFunction) {
            return $functionLike;
        }
        $stmts = [new Return_($functionLike->expr)];
        return $this->anonymousFunctionFactory->create($functionLike->params, $stmts, $functionLike->returnType, $functionLike->static);
    }
    private function isParamSkipped(Param $param) : bool
    {
        if ($param->default === null) {
            return \true;
        }
        $hasNew = (bool) $this->betterNodeFinder->findFirstInstanceOf($param->default, New_::class);
        if (!$hasNew) {
            return \true;
        }
        return $param->type instanceof IntersectionType;
    }
    /**
     * @param \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Expr\Closure|\PhpParser\Node\Stmt\Function_ $functionLike
     * @return \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Expr\Closure|\PhpParser\Node\Stmt\Function_
     */
    private function replaceNewInParams($functionLike)
    {
        $isConstructor = $functionLike instanceof ClassMethod && $this->isName($functionLike, MethodName::CONSTRUCT);
        $stmts = [];
        foreach ($functionLike->getParams() as $param) {
            if ($this->isParamSkipped($param)) {
                continue;
            }
            /** @var Expr $default */
            $default = $param->default;
            // check for property promotion
            if ($isConstructor && $param->flags > 0) {
                $propertyFetch = new PropertyFetch(new Variable('this'), $param->var->name);
                $coalesce = new Coalesce($param->var, $default);
                $assign = new Assign($propertyFetch, $coalesce);
                if ($param->type !== null) {
                    $param->type = $this->ensureNullableType($param->type);
                }
            } else {
                $assign = new AssignCoalesce($param->var, $default);
            }
            $stmts[] = new Expression($assign);
            $param->default = $this->nodeFactory->createNull();
        }
        $functionLike->stmts = $functionLike->stmts ?? [];
        $functionLike->stmts = \array_merge($stmts, $functionLike->stmts);
        return $functionLike;
    }
    /**
     * @param \PhpParser\Node\Name|\PhpParser\Node\Identifier|\PhpParser\Node\ComplexType $type
     * @return \PhpParser\Node\NullableType|\PhpParser\Node\UnionType
     */
    private function ensureNullableType($type)
    {
        if ($type instanceof NullableType) {
            return $type;
        }
        if (!$type instanceof ComplexType) {
            return new NullableType($type);
        }
        if ($type instanceof UnionType) {
            if (!$this->hasNull($type)) {
                $type->types[] = new Name('null');
            }
            return $type;
        }
        throw new ShouldNotHappenException();
    }
    private function hasNull(UnionType $unionType) : bool
    {
        foreach ($unionType->types as $type) {
            if ($type->toLowerString() === 'null') {
                return \true;
            }
        }
        return \false;
    }
}
