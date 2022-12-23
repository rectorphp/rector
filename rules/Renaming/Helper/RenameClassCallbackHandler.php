<?php

declare (strict_types=1);
namespace Rector\Renaming\Helper;

use PhpParser\Node;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\NodeVisitorAbstract;
use PHPStan\Reflection\ReflectionProvider;
use Rector\Core\Configuration\RenamedClassesDataCollector;
use Rector\NodeNameResolver\NodeNameResolver;
final class RenameClassCallbackHandler extends NodeVisitorAbstract
{
    /**
     * @var array<callable(ClassLike, NodeNameResolver, ReflectionProvider): ?string>
     */
    private $oldToNewClassCallbacks = [];
    /**
     * @readonly
     * @var \Rector\Core\Configuration\RenamedClassesDataCollector
     */
    private $renamedClassesDataCollector;
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @readonly
     * @var \PHPStan\Reflection\ReflectionProvider
     */
    private $reflectionProvider;
    public function __construct(RenamedClassesDataCollector $renamedClassesDataCollector, NodeNameResolver $nodeNameResolver, ReflectionProvider $reflectionProvider)
    {
        $this->renamedClassesDataCollector = $renamedClassesDataCollector;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->reflectionProvider = $reflectionProvider;
    }
    public function hasOldToNewClassCallbacks() : bool
    {
        return $this->oldToNewClassCallbacks !== [];
    }
    /**
     * @param array<callable(ClassLike, NodeNameResolver, ReflectionProvider): ?string> $oldToNewClassCallbacks
     */
    public function addOldToNewClassCallbacks(array $oldToNewClassCallbacks) : void
    {
        $item0Unpacked = $this->oldToNewClassCallbacks;
        $this->oldToNewClassCallbacks = \array_merge($item0Unpacked, $oldToNewClassCallbacks);
    }
    /**
     * @return array<string, string>
     */
    public function getOldToNewClassesFromNode(Node $node) : array
    {
        if ($node instanceof ClassLike) {
            return $this->handleClassLike($node);
        }
        return [];
    }
    /**
     * @return array<string, string>
     */
    private function handleClassLike(ClassLike $classLike) : array
    {
        $oldToNewClasses = [];
        $className = $classLike->name;
        if (!$className instanceof Identifier) {
            return [];
        }
        foreach ($this->oldToNewClassCallbacks as $oldToNewClassCallback) {
            $newClassName = $oldToNewClassCallback($classLike, $this->nodeNameResolver, $this->reflectionProvider);
            if ($newClassName !== null) {
                $fullyQualifiedClassName = (string) $this->nodeNameResolver->getName($classLike);
                $this->renamedClassesDataCollector->addOldToNewClass($fullyQualifiedClassName, $newClassName);
                $oldToNewClasses[$fullyQualifiedClassName] = $newClassName;
            }
        }
        return $oldToNewClasses;
    }
}
