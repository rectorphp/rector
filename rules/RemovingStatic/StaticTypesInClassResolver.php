<?php

declare (strict_types=1);
namespace Rector\RemovingStatic;

use PhpParser\Node;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Type\ObjectType;
use Rector\NodeTypeResolver\NodeTypeResolver;
use RectorPrefix20210827\Symplify\Astral\NodeTraverser\SimpleCallableNodeTraverser;
final class StaticTypesInClassResolver
{
    /**
     * @var \Symplify\Astral\NodeTraverser\SimpleCallableNodeTraverser
     */
    private $simpleCallableNodeTraverser;
    /**
     * @var \Rector\NodeTypeResolver\NodeTypeResolver
     */
    private $nodeTypeResolver;
    public function __construct(\RectorPrefix20210827\Symplify\Astral\NodeTraverser\SimpleCallableNodeTraverser $simpleCallableNodeTraverser, \Rector\NodeTypeResolver\NodeTypeResolver $nodeTypeResolver)
    {
        $this->simpleCallableNodeTraverser = $simpleCallableNodeTraverser;
        $this->nodeTypeResolver = $nodeTypeResolver;
    }
    /**
     * @param ObjectType[] $objectTypes
     * @return ObjectType[]
     */
    public function collectStaticCallTypeInClass(\PhpParser\Node\Stmt\Class_ $class, array $objectTypes) : array
    {
        $staticTypesInClass = [];
        $this->simpleCallableNodeTraverser->traverseNodesWithCallable($class->stmts, function (\PhpParser\Node $class) use($objectTypes, &$staticTypesInClass) {
            if (!$class instanceof \PhpParser\Node\Expr\StaticCall) {
                return null;
            }
            foreach ($objectTypes as $objectType) {
                if ($this->nodeTypeResolver->isObjectType($class->class, $objectType)) {
                    $staticTypesInClass[] = $objectType;
                }
            }
            return null;
        });
        return $staticTypesInClass;
    }
}
