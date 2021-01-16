<?php

declare(strict_types=1);

namespace Rector\SymfonyPHPUnit;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Stmt\Class_;
use Rector\Core\PhpParser\Node\Value\ValueResolver;
use Rector\SymfonyPHPUnit\Node\KernelTestCaseNodeAnalyzer;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symplify\Astral\NodeTraverser\SimpleCallableNodeTraverser;

final class SelfContainerMethodCallCollector
{
    /**
     * @var KernelTestCaseNodeAnalyzer
     */
    private $kernelTestCaseNodeAnalyzer;

    /**
     * @var SimpleCallableNodeTraverser
     */
    private $simpleCallableNodeTraverser;

    /**
     * @var ValueResolver
     */
    private $valueResolver;

    public function __construct(
        SimpleCallableNodeTraverser $simpleCallableNodeTraverser,
        KernelTestCaseNodeAnalyzer $kernelTestCaseNodeAnalyzer,
        ValueResolver $valueResolver
    ) {
        $this->kernelTestCaseNodeAnalyzer = $kernelTestCaseNodeAnalyzer;
        $this->simpleCallableNodeTraverser = $simpleCallableNodeTraverser;
        $this->valueResolver = $valueResolver;
    }

    /**
     * @return string[]
     */
    public function collectContainerGetServiceTypes(Class_ $class, bool $skipSetUpMethod = true): array
    {
        $serviceTypes = [];

        $this->simpleCallableNodeTraverser->traverseNodesWithCallable($class->stmts, function (Node $node) use (
            &$serviceTypes,
            $skipSetUpMethod
        ): ?Node {
            if (! $node instanceof MethodCall) {
                return null;
            }

            if (! $this->kernelTestCaseNodeAnalyzer->isOnContainerGetMethodCall($node)) {
                return null;
            }

            if ($skipSetUpMethod && $this->kernelTestCaseNodeAnalyzer->isSetUpOrEmptyMethod($node)) {
                return null;
            }

            /** @var MethodCall $node */
            $serviceType = $this->valueResolver->getValue($node->args[0]->value);
            if ($serviceType === null) {
                return null;
            }
            if (! is_string($serviceType)) {
                return null;
            }

            if ($this->shouldSkipServiceType($serviceType)) {
                return null;
            }

            $serviceTypes[] = $serviceType;

            return null;
        });

        return array_unique($serviceTypes);
    }

    private function shouldSkipServiceType(string $serviceType): bool
    {
        return $serviceType === SessionInterface::class;
    }
}
