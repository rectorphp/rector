<?php

declare(strict_types=1);

namespace Rector\Decouple\UsedNodesExtractor;

use function implode;
use PhpParser\Node;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassConst;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\PhpParser\NodeTraverser\CallableNodeTraverser;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use function sprintf;

final class UsedClassConstsExtractor
{
    /**
     * @var CallableNodeTraverser
     */
    private $callableNodeTraverser;

    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    public function __construct(CallableNodeTraverser $callableNodeTraverser, NodeNameResolver $nodeNameResolver)
    {
        $this->callableNodeTraverser = $callableNodeTraverser;
        $this->nodeNameResolver = $nodeNameResolver;
    }

    /**
     * @return ClassConst[]
     */
    public function extract(ClassMethod $classMethod): array
    {
        $classConsts = [];

        /** @var Class_ $classLike */
        $classLike = $classMethod->getAttribute(AttributeKey::CLASS_NODE);

        $this->callableNodeTraverser->traverseNodesWithCallable((array) $classMethod->stmts, function (Node $node) use (
            &$classConsts,
            $classLike
        ): ?void {
            if (! $node instanceof ClassConstFetch) {
                return null;
            }

            if (! $this->nodeNameResolver->isName($node->class, 'self')) {
                return null;
            }

            $classConstName = $this->nodeNameResolver->getName($node->name);
            if ($classConstName === null) {
                return null;
            }

            $classConsts[$classConstName] = $this->getClassConstByName($classLike, $classConstName);
        });

        return $classConsts;
    }

    private function getClassConstByName(Class_ $class, string $classConstName): ClassConst
    {
        $classConstantNames = [];
        foreach ($class->getConstants() as $constant) {
            $classConstantNames[] = $this->nodeNameResolver->getName($constant);

            if (! $this->nodeNameResolver->isName($constant, $classConstName)) {
                continue;
            }

            return $constant;
        }

        $className = $this->nodeNameResolver->getName($class);
        $message = sprintf(
            'Cannot find "%s::%s" constant. Pick one of: %s',
            $className,
            $classConstName,
            implode(', ', $classConstantNames)
        );

        throw new ShouldNotHappenException($message);
    }
}
