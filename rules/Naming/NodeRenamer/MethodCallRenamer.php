<?php

declare(strict_types=1);

namespace Rector\Naming\NodeRenamer;

use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\NodeCollector\NodeCollector\NodeRepository;

final class MethodCallRenamer
{
    public function __construct(
        private NodeRepository $nodeRepository
    ) {
    }

    public function updateClassMethodCalls(ClassMethod $classMethod, string $desiredMethodName): void
    {
        /** @var MethodCall[] $methodCalls */
        $methodCalls = $this->nodeRepository->findCallsByClassMethod($classMethod);

        foreach ($methodCalls as $methodCall) {
            $methodCall->name = new Identifier($desiredMethodName);
        }
    }
}
