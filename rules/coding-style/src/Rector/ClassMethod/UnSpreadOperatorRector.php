<?php

declare(strict_types=1);

namespace Rector\CodingStyle\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\CodingStyle\NodeAnalyzer\SpreadVariablesCollector;
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

    /**
     * @var SpreadVariablesCollector
     */
    private $spreadVariablesCollector;

    public function __construct(
        VendorFileDetector $vendorFileDetector,
        SpreadVariablesCollector $spreadVariablesCollector
    ) {
        $this->vendorFileDetector = $vendorFileDetector;
        $this->spreadVariablesCollector = $spreadVariablesCollector;
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

        $spreadVariables = $this->spreadVariablesCollector->getSpreadVariables($params);
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

        $spreadVariables = $this->spreadVariablesCollector->getSpreadVariables($args);
        if ($spreadVariables === []) {
            return null;
        }

        foreach (array_keys($spreadVariables) as $key) {
            $methodCall->args[$key]->unpack = false;
        }

        return $methodCall;
    }
}
