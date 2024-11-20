<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\TypeInferer\PropertyTypeInferer;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\Property;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Type\Type;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\PhpParser\AstResolver;
use Rector\PhpParser\Node\BetterNodeFinder;
use Rector\TypeDeclaration\TypeInferer\AssignToPropertyTypeInferer;
use Rector\ValueObject\Application\File;
final class AllAssignNodePropertyTypeInferer
{
    /**
     * @readonly
     */
    private AssignToPropertyTypeInferer $assignToPropertyTypeInferer;
    /**
     * @readonly
     */
    private NodeNameResolver $nodeNameResolver;
    /**
     * @readonly
     */
    private AstResolver $astResolver;
    /**
     * @readonly
     */
    private BetterNodeFinder $betterNodeFinder;
    public function __construct(AssignToPropertyTypeInferer $assignToPropertyTypeInferer, NodeNameResolver $nodeNameResolver, AstResolver $astResolver, BetterNodeFinder $betterNodeFinder)
    {
        $this->assignToPropertyTypeInferer = $assignToPropertyTypeInferer;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->astResolver = $astResolver;
        $this->betterNodeFinder = $betterNodeFinder;
    }
    public function inferProperty(Property $property, ClassReflection $classReflection, File $file) : ?Type
    {
        if ($classReflection->getFileName() === $file->getFilePath()) {
            $className = $classReflection->getName();
            $classLike = $this->betterNodeFinder->findFirst($file->getNewStmts(), fn(Node $node): bool => $node instanceof ClassLike && $this->nodeNameResolver->isName($node, $className));
        } else {
            $classLike = $this->astResolver->resolveClassFromClassReflection($classReflection);
        }
        if (!$classLike instanceof ClassLike) {
            return null;
        }
        $propertyName = $this->nodeNameResolver->getName($property);
        return $this->assignToPropertyTypeInferer->inferPropertyInClassLike($property, $propertyName, $classLike);
    }
}
