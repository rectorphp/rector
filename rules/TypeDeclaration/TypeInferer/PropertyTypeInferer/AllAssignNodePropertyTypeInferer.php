<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\TypeInferer\PropertyTypeInferer;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\Property;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Type\Type;
use Rector\Core\PhpParser\AstResolver;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\Core\ValueObject\Application\File;
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
     * @var \Rector\Core\PhpParser\AstResolver
     */
    private $astResolver;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
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
            $classLike = $this->betterNodeFinder->findFirst($file->getNewStmts(), function (Node $node) use($className) : bool {
                return $node instanceof ClassLike && $this->nodeNameResolver->isName($node, $className);
            });
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
