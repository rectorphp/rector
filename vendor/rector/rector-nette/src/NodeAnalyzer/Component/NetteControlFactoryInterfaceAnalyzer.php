<?php

declare (strict_types=1);
namespace Rector\Nette\NodeAnalyzer\Component;

use PhpParser\Node\Stmt\Interface_;
use PHPStan\Type\ObjectType;
use PHPStan\Type\TypeWithClassName;
use PHPStan\Type\UnionType;
use Rector\StaticTypeMapper\ValueObject\Type\ShortenedObjectType;
use Rector\TypeDeclaration\TypeInferer\ReturnTypeInferer;
final class NetteControlFactoryInterfaceAnalyzer
{
    /**
     * @var \Rector\TypeDeclaration\TypeInferer\ReturnTypeInferer
     */
    private $returnTypeInferer;
    public function __construct(\Rector\TypeDeclaration\TypeInferer\ReturnTypeInferer $returnTypeInferer)
    {
        $this->returnTypeInferer = $returnTypeInferer;
    }
    /**
     * @see https://doc.nette.org/en/3.0/components#toc-components-with-dependencies
     */
    public function isComponentFactoryInterface(\PhpParser\Node\Stmt\Interface_ $interface) : bool
    {
        foreach ($interface->getMethods() as $classMethod) {
            $returnType = $this->returnTypeInferer->inferFunctionLike($classMethod);
            if (!$returnType instanceof \PHPStan\Type\TypeWithClassName) {
                return \false;
            }
            $controlOrForm = new \PHPStan\Type\UnionType([new \PHPStan\Type\ObjectType('Nette\\Application\\UI\\Control'), new \PHPStan\Type\ObjectType('Nette\\Application\\UI\\Form')]);
            if ($returnType instanceof \Rector\StaticTypeMapper\ValueObject\Type\ShortenedObjectType) {
                $returnType = new \PHPStan\Type\ObjectType($returnType->getFullyQualifiedName());
            }
            if ($controlOrForm->isSuperTypeOf($returnType)->yes()) {
                return \true;
            }
        }
        return \false;
    }
}
