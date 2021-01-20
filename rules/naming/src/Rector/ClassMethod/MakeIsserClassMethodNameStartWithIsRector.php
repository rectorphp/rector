<?php

declare(strict_types=1);

namespace Rector\Naming\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Return_;
use PHPStan\Type\BooleanType;
use Rector\Core\Rector\AbstractRector;
use Rector\Naming\Naming\MethodNameResolver;
use Rector\Naming\NodeRenamer\MethodCallRenamer;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\Naming\Tests\Rector\ClassMethod\MakeIsserClassMethodNameStartWithIsRector\MakeIsserClassMethodNameStartWithIsRectorTest
 */
final class MakeIsserClassMethodNameStartWithIsRector extends AbstractRector
{
    /**
     * @see https://regex101.com/r/Hc73ar/1
     * @var string
     */
    private const ISSER_NAME_REGEX = '#^(is|has|was|must|does|have|should|__)#';

    /**
     * @var MethodNameResolver
     */
    private $methodNameResolver;

    /**
     * @var MethodCallRenamer
     */
    private $methodCallRenamer;

    public function __construct(MethodNameResolver $methodNameResolver, MethodCallRenamer $methodCallRenamer)
    {
        $this->methodNameResolver = $methodNameResolver;
        $this->methodCallRenamer = $methodCallRenamer;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Change is method names to start with is/has/was',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
class SomeClass
{
    /**
     * @var bool
     */
    private $isActive = false;

    public function getIsActive()
    {
        return $this->isActive;
    }
}
CODE_SAMPLE

                    ,
                    <<<'CODE_SAMPLE'
class SomeClass
{
    /**
     * @var bool
     */
    private $isActive = false;

    public function isActive()
    {
        return $this->isActive;
    }
}
CODE_SAMPLE

                ),

            ]);
    }

    /**
     * @return string[]
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
        if ($this->isAlreadyIsserNamedClassMethod($node)) {
            return null;
        }

        $getterClassMethodReturnedExpr = $this->matchIsserClassMethodReturnedExpr($node);
        if (! $getterClassMethodReturnedExpr instanceof Expr) {
            return null;
        }

        $isserMethodName = $this->methodNameResolver->resolveIsserFromReturnedExpr($getterClassMethodReturnedExpr);
        if ($isserMethodName === null) {
            return null;
        }

        if ($this->isName($node->name, $isserMethodName)) {
            return null;
        }

        $node->name = new Identifier($isserMethodName);

        $this->methodCallRenamer->updateClassMethodCalls($node, $isserMethodName);

        return $node;
    }

    private function isAlreadyIsserNamedClassMethod(ClassMethod $classMethod): bool
    {
        return $this->isName($classMethod, self::ISSER_NAME_REGEX);
    }

    private function matchIsserClassMethodReturnedExpr(ClassMethod $classMethod): ?Expr
    {
        $stmts = (array) $classMethod->stmts;
        if (count($stmts) !== 1) {
            return null;
        }

        $onlyStmt = $stmts[0] ?? null;
        if (! $onlyStmt instanceof Return_) {
            return null;
        }

        if (! $onlyStmt->expr instanceof PropertyFetch) {
            return null;
        }

        $propertyStaticType = $this->getStaticType($onlyStmt->expr);
        if (! $propertyStaticType instanceof BooleanType) {
            return null;
        }

        return $onlyStmt->expr;
    }
}
