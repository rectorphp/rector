<?php

declare(strict_types=1);

namespace Rector\DependencyInjection\NodeFactory;

use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Type\ObjectType;
use Rector\AttributeAwarePhpDoc\Ast\PhpDoc\SymfonyRequiredTagNode;
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
    /**
     * @var PhpDocInfoFactory
     */
    private $phpDocInfoFactory;

    /**
     * @var PropertyNaming
     */
    private $propertyNaming;

    /**
     * @var ClassNaming
     */
    private $classNaming;

    /**
     * @var TypeFactory
     */
    private $typeFactory;

    /**
     * @var NodeFactory
     */
    private $nodeFactory;

    public function __construct(
        ClassNaming $classNaming,
        NodeFactory $nodeFactory,
        PhpDocInfoFactory $phpDocInfoFactory,
        PropertyNaming $propertyNaming,
        TypeFactory $typeFactory
    ) {
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->propertyNaming = $propertyNaming;
        $this->classNaming = $classNaming;
        $this->typeFactory = $typeFactory;
        $this->nodeFactory = $nodeFactory;
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
            $phpDocInfo->addPhpDocTagNode(new SymfonyRequiredTagNode());
        }

        return $classMethod;
    }
}
