<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\TypeInferer\ReturnTypeInferer;

use PhpParser\Node\FunctionLike;
use PhpParser\Node\Stmt\ClassLike;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use Rector\Core\NodeManipulator\FunctionLikeManipulator;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\NodeTypeResolver\PHPStan\Type\TypeFactory;
use Rector\TypeDeclaration\Contract\TypeInferer\ReturnTypeInfererInterface;
use Rector\TypeDeclaration\TypeInferer\AssignToPropertyTypeInferer;
final class SetterNodeReturnTypeInfererTypeInferer implements \Rector\TypeDeclaration\Contract\TypeInferer\ReturnTypeInfererInterface
{
    /**
     * @readonly
     * @var \Rector\TypeDeclaration\TypeInferer\AssignToPropertyTypeInferer
     */
    private $assignToPropertyTypeInferer;
    /**
     * @readonly
     * @var \Rector\Core\NodeManipulator\FunctionLikeManipulator
     */
    private $functionLikeManipulator;
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\PHPStan\Type\TypeFactory
     */
    private $typeFactory;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    public function __construct(\Rector\TypeDeclaration\TypeInferer\AssignToPropertyTypeInferer $assignToPropertyTypeInferer, \Rector\Core\NodeManipulator\FunctionLikeManipulator $functionLikeManipulator, \Rector\NodeTypeResolver\PHPStan\Type\TypeFactory $typeFactory, \Rector\Core\PhpParser\Node\BetterNodeFinder $betterNodeFinder)
    {
        $this->assignToPropertyTypeInferer = $assignToPropertyTypeInferer;
        $this->functionLikeManipulator = $functionLikeManipulator;
        $this->typeFactory = $typeFactory;
        $this->betterNodeFinder = $betterNodeFinder;
    }
    public function inferFunctionLike(\PhpParser\Node\FunctionLike $functionLike) : \PHPStan\Type\Type
    {
        $classLike = $this->betterNodeFinder->findParentType($functionLike, \PhpParser\Node\Stmt\ClassLike::class);
        if (!$classLike instanceof \PhpParser\Node\Stmt\ClassLike) {
            return new \PHPStan\Type\MixedType();
        }
        $returnedPropertyNames = $this->functionLikeManipulator->getReturnedLocalPropertyNames($functionLike);
        $types = [];
        foreach ($returnedPropertyNames as $returnedPropertyName) {
            $inferredPropertyType = $this->assignToPropertyTypeInferer->inferPropertyInClassLike($returnedPropertyName, $classLike);
            if (!$inferredPropertyType instanceof \PHPStan\Type\Type) {
                continue;
            }
            $types[] = $inferredPropertyType;
        }
        return $this->typeFactory->createMixedPassedOrUnionType($types);
    }
    public function getPriority() : int
    {
        return 600;
    }
}
