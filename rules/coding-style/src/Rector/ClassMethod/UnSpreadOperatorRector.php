<?php

declare(strict_types=1);

namespace Rector\CodingStyle\Rector\ClassMethod;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Type\ObjectType;
use PHPStan\Type\ThisType;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use ReflectionClass;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\CodingStyle\Tests\Rector\ClassMethod\UnSpreadOperatorRector\UnSpreadOperatorRectorTest
 */
final class UnSpreadOperatorRector extends AbstractRector
{
    /**
     * @see https://regex101.com/r/ChpDsj/1
     * @var string
     */
    private const ANONYMOUS_CLASS_REGEX = '#^AnonymousClass[\w+]#';

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Remove spread operator', [
            new CodeSample(
                <<<'CODE_SAMPLE'
class SomeClass
{
    public function run(...$array)
    {
    }

    public function execute(array $data)
    {
        $this->run(...$data);
    }
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
class SomeClass
{
    public function run(array $array)
    {
    }

    public function execute(array $data)
    {
        $this->run($data);
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
        return [ClassMethod::class, MethodCall::class];
    }

    /**
     * @param ClassMethod|MethodCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($node instanceof ClassMethod) {
            return $this->processUnspreadOperatorClassMethodParams($node);
        }

        return $this->processUnspreadOperatorMethodCallArgs($node);
    }

    private function getClassFileNameByClassMethod(ClassMethod $classMethod): ?string
    {
        $parent = $classMethod->getAttribute(AttributeKey::PARENT_NODE);
        if (! $parent instanceof Class_) {
            return null;
        }

        $reflectionClass = new ReflectionClass((string) $parent->namespacedName);
        return (string) $reflectionClass->getFileName();
    }

    private function getClassFileNameByMethodCall(MethodCall $methodCall): ?string
    {
        $scope = $methodCall->getAttribute(AttributeKey::SCOPE);
        if ($scope === null) {
            return null;
        }

        $type = $scope->getType($methodCall->var);
        if ($type instanceof ObjectType) {
            $classReflection = $type->getClassReflection();
            return (string) $classReflection->getFileName();
        }

        if ($type instanceof ThisType) {
            $staticObjectType = $type->getStaticObjectType();
            $classReflection = $staticObjectType->getClassReflection();
            return (string) $classReflection->getFileName();
        }

        return null;
    }

    private function processUnspreadOperatorClassMethodParams(ClassMethod $classMethod): ?ClassMethod
    {
        if ($this->isInVendor($classMethod)) {
            return null;
        }

        $params = $classMethod->params;
        if ($params === []) {
            return null;
        }

        $spreadVariables = $this->getSpreadVariables($params);
        if ($spreadVariables === []) {
            return null;
        }

        foreach (array_keys($spreadVariables) as $key) {
            $classMethod->params[$key]->variadic = false;
            $classMethod->params[$key]->type = new Identifier('array');
        }

        return $classMethod;
    }

    /**
     * @param ClassMethod|MethodCall $node
     */
    private function isInVendor(Node $node): bool
    {
        $scope = $node->getAttribute(AttributeKey::SCOPE);
        $fileName = $node instanceof ClassMethod
            ? $this->getClassFileNameByClassMethod($node)
            : $this->getClassFileNameByMethodCall($node);

        return Strings::contains($fileName, 'vendor');
    }

    private function processUnspreadOperatorMethodCallArgs(MethodCall $methodCall): ?MethodCall
    {
        if ($this->isInVendor($methodCall)) {
            return null;
        }

        $args = $methodCall->args;
        if ($args === []) {
            return null;
        }

        $spreadVariables = $this->getSpreadVariables($args);
        if ($spreadVariables === []) {
            return null;
        }

        foreach (array_keys($spreadVariables) as $key) {
            $methodCall->args[$key]->unpack = false;
        }

        return $methodCall;
    }

    /**
     * @param Param[]|Arg[] $array
     * @return Param[]|Arg[]
     */
    private function getSpreadVariables(array $array): array
    {
        $spreadVariables = [];
        foreach ($array as $key => $paramOrArg) {
            if ($paramOrArg instanceof Param) {
                if (! $paramOrArg->variadic) {
                    continue;
                }

                if ($paramOrArg->type !== null) {
                    continue;
                }
            }

            if ($paramOrArg instanceof Arg && ! $paramOrArg->unpack) {
                continue;
            }

            $spreadVariables[$key] = $paramOrArg;
        }

        return $spreadVariables;
    }
}
