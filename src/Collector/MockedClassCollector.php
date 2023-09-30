<?php

declare (strict_types=1);
namespace Rector\Core\Collector;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use PHPStan\Analyser\Scope;
use PHPStan\Collectors\Collector;
use PHPStan\Type\Constant\ConstantStringType;
/**
 * @implements Collector<MethodCall, string[]|null>
 */
final class MockedClassCollector implements Collector
{
    public function getNodeType() : string
    {
        return MethodCall::class;
    }
    /**
     * @param MethodCall $node
     * @return string[]|null
     */
    public function processNode(Node $node, Scope $scope) : ?array
    {
        if (!$node->name instanceof Identifier) {
            return null;
        }
        $methodName = $node->name->toString();
        if (!\in_array($methodName, ['createMock', 'buildMock'], \true)) {
            return null;
        }
        $firstArg = $node->getArgs()[0] ?? null;
        if (!$firstArg instanceof Arg) {
            return null;
        }
        $mockedClassType = $scope->getType($firstArg->value);
        if (!$mockedClassType instanceof ConstantStringType) {
            return null;
        }
        return [$mockedClassType->getValue()];
    }
}
