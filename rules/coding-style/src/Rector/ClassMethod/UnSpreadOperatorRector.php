<?php

declare(strict_types=1);

namespace Rector\CodingStyle\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Core\Rector\AbstractRector;
use Rector\VendorLocker\VendorFileDetector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\CodingStyle\Tests\Rector\ClassMethod\UnSpreadOperatorRector\UnSpreadOperatorRectorTest
 */
final class UnSpreadOperatorRector extends AbstractRector
{
    /**
     * @var VendorFileDetector
     */
    private $vendorFileDetector;

    public function __construct(VendorFileDetector $vendorFileDetector)
    {
        $this->vendorFileDetector = $vendorFileDetector;
    }

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

    private function processUnspreadOperatorClassMethodParams(ClassMethod $classMethod): ?ClassMethod
    {
        if ($this->vendorFileDetector->isNodeInVendor($classMethod)) {
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
        if ($this->vendorFileDetector->isNodeInVendor($methodCall)) {
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
            if ($paramOrArg instanceof Param && (! $paramOrArg->variadic || $paramOrArg->type !== null)) {
                continue;
            }

            if ($paramOrArg instanceof Arg && (! $paramOrArg->unpack || ! $paramOrArg->value instanceof Variable)) {
                continue;
            }

            $spreadVariables[$key] = $paramOrArg;
        }

        return $spreadVariables;
    }
}
