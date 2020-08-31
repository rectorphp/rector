<?php

declare(strict_types=1);

namespace Rector\SymfonyPHPUnit;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Stmt\Class_;
use Rector\Core\PhpParser\Node\Value\ValueResolver;
use Rector\Core\PhpParser\NodeTraverser\CallableNodeTraverser;
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
        CallableNodeTraverser $callableNodeTraverser,
        KernelTestCaseNodeAnalyzer $kernelTestCaseNodeAnalyzer,
        ValueResolver $valueResolver
    ) {
        $this->kernelTestCaseNodeAnalyzer = $kernelTestCaseNodeAnalyzer;
        $this->callableNodeTraverser = $callableNodeTraverser;
        $this->valueResolver = $valueResolver;
    }

    /**
     * @return string[]
     */
    public function collectContainerGetServiceTypes(Class_ $class, bool $skipSetUpMethod = true): array
    {
        $serviceTypes = [];

        $this->callableNodeTraverser->traverseNodesWithCallable($class->stmts, function (Node $node) use (
            &$serviceTypes,
            $skipSetUpMethod
        ): ?void {
            if (! $this->kernelTestCaseNodeAnalyzer->isOnContainerGetMethodCall($node)) {
                return null;
            }

            if ($skipSetUpMethod && $this->kernelTestCaseNodeAnalyzer->isSetUpOrEmptyMethod($node)) {
                return null;
            }

            /** @var MethodCall $node */
            $serviceType = $this->valueResolver->getValue($node->args[0]->value);
            if ($serviceType === null || ! is_string($serviceType)) {
                return null;
            }

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
