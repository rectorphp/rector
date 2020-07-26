<?php

declare(strict_types=1);

namespace Rector\NetteCodeQuality\FormControlTypeResolver;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Return_;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\NetteCodeQuality\Contract\FormControlTypeResolverInterface;
use Rector\NetteCodeQuality\Contract\MethodNamesByInputNamesResolverAwareInterface;
use Rector\NetteCodeQuality\NodeResolver\MethodNamesByInputNamesResolver;

final class ReturnFormControlTypeResolver implements FormControlTypeResolverInterface, MethodNamesByInputNamesResolverAwareInterface
{
    /**
     * @var BetterNodeFinder
     */
    private $betterNodeFinder;

    /**
     * @var MethodNamesByInputNamesResolver
     */
    private $methodNamesByInputNamesResolver;

    public function __construct(BetterNodeFinder $betterNodeFinder)
    {
        $this->betterNodeFinder = $betterNodeFinder;
    }

    /**
     * @return array<string, string>
     */
    public function resolve(Node $node): array
    {
        if (! $node instanceof Return_) {
            return [];
        }

        if (! $node->expr instanceof Variable) {
            return [];
        }

        $initialAssign = $this->betterNodeFinder->findPreviousAssignToExpr($node->expr);
        if (! $initialAssign instanceof Assign) {
            return [];
        }

        return $this->methodNamesByInputNamesResolver->resolveExpr($node);
    }

    public function setResolver(MethodNamesByInputNamesResolver $methodNamesByInputNamesResolver): void
    {
        $this->methodNamesByInputNamesResolver = $methodNamesByInputNamesResolver;
    }
}
