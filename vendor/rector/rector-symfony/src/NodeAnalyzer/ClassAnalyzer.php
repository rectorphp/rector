<?php

declare (strict_types=1);
namespace Rector\Symfony\NodeAnalyzer;

use PhpParser\Node\Stmt\Class_;
use Rector\NodeNameResolver\NodeNameResolver;
final class ClassAnalyzer
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
    public function hasImplements(Class_ $class, string $interfaceFQN) : bool
    {
        foreach ($class->implements as $implement) {
            if ($this->nodeNameResolver->isName($implement, $interfaceFQN)) {
                return \true;
            }
        }
        return \false;
    }
}
