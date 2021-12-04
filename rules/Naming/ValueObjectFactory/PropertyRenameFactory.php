<?php

declare (strict_types=1);
namespace Rector\Naming\ValueObjectFactory;

use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\Property;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\Naming\ValueObject\PropertyRename;
use Rector\NodeNameResolver\NodeNameResolver;
/**
 * @see \Rector\Tests\Naming\ValueObjectFactory\PropertyRenameFactory\PropertyRenameFactoryTest
 */
final class PropertyRenameFactory
{
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    public function __construct(\Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver, \Rector\Core\PhpParser\Node\BetterNodeFinder $betterNodeFinder)
    {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->betterNodeFinder = $betterNodeFinder;
    }
    public function createFromExpectedName(\PhpParser\Node\Stmt\Property $property, string $expectedName) : ?\Rector\Naming\ValueObject\PropertyRename
    {
        $currentName = $this->nodeNameResolver->getName($property);
        $classLike = $this->betterNodeFinder->findParentType($property, \PhpParser\Node\Stmt\ClassLike::class);
        if (!$classLike instanceof \PhpParser\Node\Stmt\ClassLike) {
            return null;
        }
        $className = (string) $this->nodeNameResolver->getName($classLike);
        return new \Rector\Naming\ValueObject\PropertyRename($property, $expectedName, $currentName, $classLike, $className, $property->props[0]);
    }
}
