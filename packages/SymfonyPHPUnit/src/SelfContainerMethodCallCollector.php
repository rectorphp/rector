<?php

declare(strict_types=1);

namespace Rector\SymfonyPHPUnit;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Stmt\Class_;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PhpParser\Node\Value\ValueResolver;
use Rector\PhpParser\NodeTraverser\CallableNodeTraverser;
use Rector\SymfonyPHPUnit\Node\KernelTestCaseNodeAnalyzer;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

final class SelfContainerMethodCallCollector
{
    /**
     * @var KernelTestCaseNodeAnalyzer
     */
    private $kernelTestCaseNodeAnalyzer;

    /**
     * @var CallableNodeTraverser
     */
    private $callableNodeTraverser;

    /**
     * @var ValueResolver
     */
    private $valueResolver;

    public function __construct(
        KernelTestCaseNodeAnalyzer $kernelTestCaseNodeAnalyzer,
        CallableNodeTraverser $callableNodeTraverser,
        ValueResolver $valueResolver
    ) {
        $this->kernelTestCaseNodeAnalyzer = $kernelTestCaseNodeAnalyzer;
        $this->callableNodeTraverser = $callableNodeTraverser;
        $this->valueResolver = $valueResolver;
    }

    /**
     * @return string[]
     */
    public function collectContainerGetServiceTypes(Class_ $class): array
    {
        $serviceTypes = [];

        $this->callableNodeTraverser->traverseNodesWithCallable($class->stmts, function (Node $node) use (
            &$serviceTypes
        ) {
            if (! $this->kernelTestCaseNodeAnalyzer->isSelfContainerGetMethodCall($node)) {
                return null;
            }

            // skip setUp() method
            $methodName = $node->getAttribute(AttributeKey::METHOD_NAME);
            if ($methodName === 'setUp' || $methodName === null) {
                return null;
            }

            /** @var MethodCall $node */
            $serviceType = $this->valueResolver->getValue($node->args[0]->value);
            if ($this->shouldSkipServiceType($serviceType)) {
                return null;
            }

            $serviceTypes[] = $serviceType;
        });

        return array_unique($serviceTypes);
    }

    private function shouldSkipServiceType(string $serviceType): bool
    {
        return $serviceType === SessionInterface::class;
    }
}
