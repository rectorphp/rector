<?php

declare(strict_types=1);

namespace Rector\Naming\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Return_;
use PHPStan\Type\BooleanType;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\Naming\Naming\MethodNameResolver;

/**
 * @see \Rector\Naming\Tests\Rector\ClassMethod\MakeGetterClassMethodNameStartWithGetRector\MakeGetterClassMethodNameStartWithGetRectorTest
 */
final class MakeGetterClassMethodNameStartWithGetRector extends AbstractRector
{
    /**
     * @var string
     */
    private const GETTER_NAME_PATTERN = '#^(is|should|has|was|must|get|provide|__)#';

    /**
     * @var MethodNameResolver
     */
    private $methodNameResolver;

    public function __construct(MethodNameResolver $methodNameResolver)
    {
        $this->methodNameResolver = $methodNameResolver;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Change getter method names to start with get/provide', [
            new CodeSample(
                <<<'CODE_SAMPLE'
class SomeClass
{
    /**
     * @var string
     */
    private $name;

    public function name(): string
    {
        return $this->name;
    }
}
CODE_SAMPLE

                ,
                <<<'CODE_SAMPLE'
class SomeClass
{
    /**
     * @var string
     */
    private $name;

    public function getName(): string
    {
        return $this->name;
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
        if ($this->isAlreadyGetterNamedClassMethod($node)) {
            return null;
        }

        $getterClassMethodReturnedExpr = $this->matchGetterClassMethodReturnedExpr($node);
        if ($getterClassMethodReturnedExpr === null) {
            return null;
        }

        $getterMethodName = $this->methodNameResolver->resolveGetterFromReturnedExpr($getterClassMethodReturnedExpr);
        if ($getterMethodName === null) {
            return null;
        }

        if ($this->isName($node->name, $getterMethodName)) {
            return null;
        }

        $node->name = new Identifier($getterMethodName);

        $this->updateClassMethodCalls($node, $getterMethodName);

        return $node;
    }

    private function isAlreadyGetterNamedClassMethod(ClassMethod $classMethod): bool
    {
        return $this->isName($classMethod, self::GETTER_NAME_PATTERN);
    }

    private function matchGetterClassMethodReturnedExpr(ClassMethod $classMethod): ?Expr
    {
        if (count((array) $classMethod->stmts) !== 1) {
            return null;
        }

        $onlyStmt = $classMethod->stmts[0];
        if (! $onlyStmt instanceof Return_) {
            return null;
        }

        if (! $onlyStmt->expr instanceof PropertyFetch) {
            return null;
        }

        $propertyStaticType = $this->getStaticType($onlyStmt->expr);
        // is handled by boolish Rector → skip here
        if ($propertyStaticType instanceof BooleanType) {
            return null;
        }

        return $onlyStmt->expr;
    }

    private function updateClassMethodCalls(ClassMethod $classMethod, string $newClassMethodName): void
    {
        /** @var MethodCall[] $methodCalls */
        $methodCalls = $this->nodeRepository->findCallsByClassMethod($classMethod);
        foreach ($methodCalls as $methodCall) {
            $methodCall->name = new Identifier($newClassMethodName);
        }
    }
}
