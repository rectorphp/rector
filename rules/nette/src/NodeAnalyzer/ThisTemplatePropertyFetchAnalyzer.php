<?php

declare(strict_types=1);

namespace Rector\Nette\NodeAnalyzer;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use Rector\NodeNameResolver\NodeNameResolver;

final class ThisTemplatePropertyFetchAnalyzer
{
    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    public function __construct(NodeNameResolver $nodeNameResolver)
    {
        $this->nodeNameResolver = $nodeNameResolver;
    }

    /**
     * $this->template->someKey => "someKey"
     */
    public function matchThisTemplateKey(Expr $expr): ?string
    {
        if (! $expr instanceof PropertyFetch) {
            return null;
        }

        if (! $expr->var instanceof PropertyFetch) {
            return null;
        }

        if (! $this->nodeNameResolver->isName($expr->var, 'template')) {
            return null;
        }

        return $this->nodeNameResolver->getName($expr->name);
    }

    /**
     * Looks for:
     * $this->template
     *
     * $template
     */
    public function isTemplatePropertyFetch(Expr $expr): bool
    {
        if (! $expr instanceof PropertyFetch) {
            return false;
        }

        if (! $expr->var instanceof Variable) {
            return false;
        }

        if (! $this->nodeNameResolver->isName($expr->var, 'this')) {
            return false;
        }

        return $this->nodeNameResolver->isName($expr->name, 'template');
    }
}
