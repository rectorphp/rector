<?php
declare(strict_types=1);

namespace Rector\Nette\NodeAnalyzer;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\StaticCall;
use Rector\NodeNameResolver\NodeNameResolver;

final class StaticCallAnalyzer
{
    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    public function __construct(NodeNameResolver $nodeNameResolver)
    {
        $this->nodeNameResolver = $nodeNameResolver;
    }

    public function isParentCallNamed(StaticCall $staticCall, string $desiredMethodName): bool
    {
        if ($staticCall->class instanceof Expr) {
            return false;
        }

        if (! $this->nodeNameResolver->isName($staticCall->class, 'parent')) {
            return false;
        }

        if ($staticCall->name instanceof Expr) {
            return false;
        }

        return $this->nodeNameResolver->isName($staticCall->name, $desiredMethodName);
    }
}
