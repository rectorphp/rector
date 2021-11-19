<?php

declare(strict_types=1);

namespace Rector\DowngradePhp81\Rector\FunctionLike;

use PhpParser\Node;
use PhpParser\Node\ComplexType;
use PhpParser\Node\Expr\ArrowFunction;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\AssignOp\Coalesce as AssignCoalesce;
use PhpParser\Node\Expr\BinaryOp\Coalesce;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Identifier;
use PhpParser\Node\IntersectionType;
use PhpParser\Node\Name;
use PhpParser\Node\NullableType;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Return_;
use PhpParser\Node\UnionType;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\MethodName;
use Rector\Php72\NodeFactory\AnonymousFunctionFactory;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @changelog https://wiki.php.net/rfc/new_in_initializers
 *
 * @see \Rector\Tests\DowngradePhp81\Rector\FunctionLike\DowngradeNewInInitializerRector\DowngradeNewInInitializerRectorTest
 */
final class DowngradeNewInInitializerRector extends AbstractRector
{
    public function __construct(
        private AnonymousFunctionFactory $anonymousFunctionFactory
    ) {
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Replace New in initializers', [
            new CodeSample(
                <<<'CODE_SAMPLE'
class SomeClass
{
    public function __construct(
        private Logger $logger = new NullLogger,
    ) {
    }
}
CODE_SAMPLE

                ,
                <<<'CODE_SAMPLE'
class SomeClass
{
    public function __construct(
        private ?Logger $logger = null,
    ) {
        $this->logger = $logger ?? new NullLogger;
    }
}
CODE_SAMPLE
            ),
        ]);
    }

    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [FunctionLike::class];
    }

    /**
     * @param FunctionLike $node
     */
    public function refactor(Node $node): ?FunctionLike
    {
        if ($this->shouldSkip($node)) {
            return null;
        }

        $node = $this->convertArrowFunctionToClosure($node);

        return $this->replaceNewInParams($node);
    }

    private function shouldSkip(FunctionLike $functionLike): bool
    {
        foreach ($functionLike->getParams() as $param) {
            if (! $param->default instanceof New_) {
                continue;
            }
            if ($param->type instanceof IntersectionType) {
                continue;
            }
            return false;
        }

        return true;
    }

    private function convertArrowFunctionToClosure(FunctionLike $functionLike): FunctionLike
    {
        if (! $functionLike instanceof ArrowFunction) {
            return $functionLike;
        }

        $stmts = [new Return_($functionLike->expr)];

        return $this->anonymousFunctionFactory->create(
            $functionLike->params,
            $stmts,
            $functionLike->returnType,
            $functionLike->static
        );
    }

    private function replaceNewInParams(FunctionLike $functionLike): FunctionLike
    {
        $isConstructor = $functionLike instanceof ClassMethod && $this->isName($functionLike, MethodName::CONSTRUCT);

        $stmts = [];
        foreach ($functionLike->getParams() as $param) {
            if (! $param->default instanceof New_) {
                continue;
            }

            // check for property promotion
            if ($isConstructor && $param->flags > 0) {
                $propertyFetch = new PropertyFetch(new Variable('this'), $param->var->name);
                $coalesce = new Coalesce($param->var, $param->default);
                $assign = new Assign($propertyFetch, $coalesce);

                if ($param->type !== null) {
                    $param->type = $this->ensureNullableType($param->type);
                }
            } else {
                $assign = new AssignCoalesce($param->var, $param->default);
            }

            $stmts[] = new Expression($assign);

            $param->default = $this->nodeFactory->createNull();
        }

        $functionLike->stmts ??= [];
        $functionLike->stmts = array_merge($stmts, $functionLike->stmts);

        return $functionLike;
    }

    private function ensureNullableType(Name|Identifier|ComplexType $type): NullableType|UnionType
    {
        if ($type instanceof NullableType) {
            return $type;
        }

        if (! $type instanceof ComplexType) {
            return new NullableType($type);
        }

        if ($type instanceof UnionType) {
            if (! $this->hasNull($type)) {
                $type->types[] = new Name('null');
            }

            return $type;
        }

        throw new ShouldNotHappenException();
    }

    private function hasNull(UnionType $unionType): bool
    {
        foreach ($unionType->types as $type) {
            if ($type->toLowerString() === 'null') {
                return true;
            }
        }

        return false;
    }
}
