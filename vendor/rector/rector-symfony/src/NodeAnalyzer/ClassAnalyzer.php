<?php

declare (strict_types=1);
namespace Rector\Symfony\NodeAnalyzer;

use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Class_;
use Rector\NodeNameResolver\NodeNameResolver;
final class ClassAnalyzer
{
    /**
     * @readonly
     */
    private NodeNameResolver $nodeNameResolver;
    public function __construct(NodeNameResolver $nodeNameResolver)
    {
        $this->nodeNameResolver = $nodeNameResolver;
    }
    public function hasImplements(Class_ $class, string $interfaceFQN): bool
    {
        $found = \false;
        foreach ($class->implements as $name) {
            if ($this->nodeNameResolver->isName($name, $interfaceFQN)) {
                $found = \true;
                break;
            }
        }
        return $found;
    }
}
