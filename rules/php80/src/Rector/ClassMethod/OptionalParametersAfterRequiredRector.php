<?php

declare(strict_types=1);

namespace Rector\Php80\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Type\TypeWithClassName;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\MethodName;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Php80\NodeResolver\RequireOptionalParamResolver;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see https://php.watch/versions/8.0#deprecate-required-param-after-optional
 *
 * @see \Rector\Php80\Tests\Rector\ClassMethod\OptionalParametersAfterRequiredRector\OptionalParametersAfterRequiredRectorTest
 */
final class OptionalParametersAfterRequiredRector extends AbstractRector
{
    /**
     * @var RequireOptionalParamResolver
     */
    private $requireOptionalParamResolver;

    public function __construct(RequireOptionalParamResolver $requireOptionalParamResolver)
    {
        $this->requireOptionalParamResolver = $requireOptionalParamResolver;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Move required parameters after optional ones', [
            new CodeSample(
                <<<'CODE_SAMPLE'
class SomeObject
{
    public function run($optional = 1, $required)
    {
    }
}
CODE_SAMPLE

                ,
                <<<'CODE_SAMPLE'
class SomeObject
{
    public function run($required, $optional = 1)
    {
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
        return [ClassMethod::class, New_::class];
    }

    /**
     * @param ClassMethod|New_ $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($node instanceof ClassMethod) {
            return $this->refactorClassMethod($node);
        }

        return $this->refactorNew($node);
    }

    private function refactorClassMethod(ClassMethod $node): ?ClassMethod
    {
        if ($node->params === []) {
            return null;
        }

        $expectedOrderParams = $this->requireOptionalParamResolver->resolve($node);
        if ($node->params === $expectedOrderParams) {
            return null;
        }

        $node->params = $expectedOrderParams;

        return $node;
    }

    private function refactorNew(New_ $new): ?New_
    {
        if ($new->args === []) {
            return null;
        }

        $constructorClassMethod = $this->findClassMethodConstructorByNew($new);
        if (! $constructorClassMethod instanceof ClassMethod) {
            return null;
        }

        // we need orignal node, as the order might have already hcanged
        $originalClassMethod = $constructorClassMethod->getAttribute(AttributeKey::ORIGINAL_NODE);
        if (! $originalClassMethod instanceof ClassMethod) {
            return null;
        }

        $expectedOrderedParams = $this->requireOptionalParamResolver->resolve($originalClassMethod);
        if ($expectedOrderedParams === $originalClassMethod->getParams()) {
            return null;
        }

        $newArgs = $this->resolveNewArgsOrderedByRequiredParams($expectedOrderedParams, $new);
        if ($new->args === $newArgs) {
            return null;
        }

        $new->args = $newArgs;
        return $new;
    }

    private function findClassMethodConstructorByNew(New_ $new): ?ClassMethod
    {
        $className = $this->getObjectType($new->class);
        if (! $className instanceof TypeWithClassName) {
            return null;
        }

        $constructorClassMethod = $this->nodeRepository->findClassMethod(
            $className->getClassName(),
            MethodName::CONSTRUCT
        );
        if ($constructorClassMethod === null) {
            return null;
        }

        if ($constructorClassMethod->getParams() === []) {
            return null;
        }

        return $constructorClassMethod;
    }

    /**
     * @param array<int, Param> $expectedOrderedParams
     * @return array<int, Arg>
     */
    private function resolveNewArgsOrderedByRequiredParams(array $expectedOrderedParams, New_ $new): array
    {
        $oldToNewPositions = array_keys($expectedOrderedParams);

        $newArgs = [];
        foreach ($new->args as $position => $arg) {
            $newPosition = $oldToNewPositions[$position] ?? null;
            if ($newPosition === null) {
                continue;
            }

            $newArgs[$position] = $new->args[$newPosition];
        }
        return $newArgs;
    }
}
