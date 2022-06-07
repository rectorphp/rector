<?php

declare (strict_types=1);
namespace Rector\DeadCode\NodeAnalyzer;

use PhpParser\Node\Expr\Instanceof_;
use PhpParser\Node\Expr\Variable;
use Rector\NodeNameResolver\NodeNameResolver;
final class InstanceOfUniqueKeyResolver
{
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    public function __construct(NodeNameResolver $nodeNameResolver)
    {
        $this->nodeNameResolver = $nodeNameResolver;
    }
    public function resolve(Instanceof_ $instanceof) : ?string
    {
        if (!$instanceof->expr instanceof Variable) {
            return null;
        }
        $variableName = $this->nodeNameResolver->getName($instanceof->expr);
        if ($variableName === null) {
            return null;
        }
        $className = $this->nodeNameResolver->getName($instanceof->class);
        if ($className === null) {
            return null;
        }
        return $variableName . '_' . $className;
    }
}
