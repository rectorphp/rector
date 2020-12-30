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

    private function getClassName(ClassMethod $classMethod): ?string
    {
        $parent = $classMethod->getAttribute(AttributeKey::PARENT_NODE);
        if (! $parent instanceof Class_) {
            return null;
        }

        $shortClassName = (string) $parent->name;
        if (Strings::match($shortClassName, self::ANONYMOUS_CLASS_REGEX)) {
            return [];
        }

        return (string) $parent->namespacedName;
    }

    private function processUnspreadOperatorClassMethodParams(ClassMethod $classMethod): ?ClassMethod
    {
        $className = $this->getClassName($classMethod);
        $reflectionClass = new ReflectionClass($className);
        $fileName = $reflectionClass->getFileName();

        if (Strings::contains($fileName, 'vendor')) {
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

    private function processUnspreadOperatorMethodCallArgs(MethodCall $methodCall): ?MethodCall
    {
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
