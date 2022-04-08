<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\TypeInferer\PropertyTypeInferer;

use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\Property;
use PHPStan\Type\Type;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\TypeDeclaration\TypeInferer\AssignToPropertyTypeInferer;
final class AllAssignNodePropertyTypeInferer
{
    /**
     * @readonly
     * @var \Rector\TypeDeclaration\TypeInferer\AssignToPropertyTypeInferer
     */
    private $assignToPropertyTypeInferer;
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
    public function __construct(\Rector\TypeDeclaration\TypeInferer\AssignToPropertyTypeInferer $assignToPropertyTypeInferer, \Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver, \Rector\Core\PhpParser\Node\BetterNodeFinder $betterNodeFinder)
    {
        $this->assignToPropertyTypeInferer = $assignToPropertyTypeInferer;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->betterNodeFinder = $betterNodeFinder;
    }
    public function inferProperty(\PhpParser\Node\Stmt\Property $property) : ?\PHPStan\Type\Type
    {
        $classLike = $this->betterNodeFinder->findParentType($property, \PhpParser\Node\Stmt\ClassLike::class);
        if (!$classLike instanceof \PhpParser\Node\Stmt\ClassLike) {
            return null;
        }
        $propertyName = $this->nodeNameResolver->getName($property);
        return $this->assignToPropertyTypeInferer->inferPropertyInClassLike($property, $propertyName, $classLike);
    }
}
