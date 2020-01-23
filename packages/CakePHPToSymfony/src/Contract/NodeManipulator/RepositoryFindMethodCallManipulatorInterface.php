<?php

declare(strict_types=1);

namespace Rector\CakePHPToSymfony\Contract\NodeManipulator;

use PhpParser\Node\Expr\MethodCall;

interface RepositoryFindMethodCallManipulatorInterface
{
    public function getKeyName(): string;

    public function processMethodCall(MethodCall $methodCall, string $entityClass): MethodCall;
}
