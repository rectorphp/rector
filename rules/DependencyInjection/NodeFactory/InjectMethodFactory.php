<?php

declare(strict_types=1);

namespace Rector\DependencyInjection\NodeFactory;

use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\PhpDocParser\Ast\PhpDoc\GenericTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use PHPStan\Type\ObjectType;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\CodingStyle\Naming\ClassNaming;
use Rector\Core\PhpParser\Node\NodeFactory;
use Rector\Core\ValueObject\FrameworkName;
use Rector\Naming\Naming\PropertyNaming;
use Rector\NodeTypeResolver\PHPStan\Type\TypeFactory;
use Symplify\Astral\ValueObject\NodeBuilder\MethodBuilder;
use Symplify\Astral\ValueObject\NodeBuilder\ParamBuilder;

final class InjectMethodFactory
{
    public function __construct(
        private ClassNaming $classNaming,
        private NodeFactory $nodeFactory,
        private PhpDocInfoFactory $phpDocInfoFactory,
        private PropertyNaming $propertyNaming,
        private TypeFactory $typeFactory
    ) {
    }

    /**
     * @param ObjectType[] $objectTypes
     */
    public function createFromTypes(array $objectTypes, string $className, string $framework): ClassMethod
    {
        $objectTypes = $this->typeFactory->uniquateTypes($objectTypes);

        $shortClassName = $this->classNaming->getShortName($className);

        $methodBuilder = new MethodBuilder('inject' . $shortClassName);
        $methodBuilder->makePublic();

        foreach ($objectTypes as $objectType) {
            /** @var ObjectType $objectType */
            $propertyName = $this->propertyNaming->fqnToVariableName($objectType);

            $paramBuilder = new ParamBuilder($propertyName);
            $paramBuilder->setType(new FullyQualified($objectType->getClassName()));
            $methodBuilder->addParam($paramBuilder);

            $assign = $this->nodeFactory->createPropertyAssignment($propertyName);

            $methodBuilder->addStmt($assign);
        }

        $classMethod = $methodBuilder->getNode();

        if ($framework === FrameworkName::SYMFONY) {
            $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($classMethod);
            $phpDocInfo->addPhpDocTagNode(new PhpDocTagNode('@required', new GenericTagValueNode('')));
        }

        return $classMethod;
    }
}
