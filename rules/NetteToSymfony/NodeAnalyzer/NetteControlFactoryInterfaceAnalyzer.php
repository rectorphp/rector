<?php

declare(strict_types=1);

namespace Rector\NetteToSymfony\NodeAnalyzer;

use PhpParser\Node\Stmt\Interface_;
use PHPStan\Type\ObjectType;
use PHPStan\Type\TypeWithClassName;
use PHPStan\Type\UnionType;
use Rector\StaticTypeMapper\ValueObject\Type\ShortenedObjectType;
use Rector\TypeDeclaration\TypeInferer\ReturnTypeInferer;

final class NetteControlFactoryInterfaceAnalyzer
{
    /**
     * @var ReturnTypeInferer
     */
    private $returnTypeInferer;

    public function __construct(ReturnTypeInferer $returnTypeInferer)
    {
        $this->returnTypeInferer = $returnTypeInferer;
    }

    /**
     * @see https://doc.nette.org/en/3.0/components#toc-components-with-dependencies
     */
    public function isComponentFactoryInterface(Interface_ $interface): bool
    {
        foreach ($interface->getMethods() as $classMethod) {
            $returnType = $this->returnTypeInferer->inferFunctionLike($classMethod);
            if (! $returnType instanceof TypeWithClassName) {
                return false;
            }

            $controlOrForm = new UnionType([
                new ObjectType('Nette\Application\UI\Control'),
                new ObjectType('Nette\Application\UI\Form'),
            ]);

            if ($returnType instanceof ShortenedObjectType) {
                $returnType = new ObjectType($returnType->getFullyQualifiedName());
            }

            if ($controlOrForm->isSuperTypeOf($returnType)->yes()) {
                return true;
            }
        }

        return false;
    }
}
