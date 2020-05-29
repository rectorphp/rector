<?php

declare(strict_types=1);

namespace Rector\SOLID\NodeFactory;

use PhpParser\Builder\Method;
use PhpParser\Builder\Param;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Type\ObjectType;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\CodingStyle\Naming\ClassNaming;
use Rector\Core\Naming\PropertyNaming;
use Rector\Core\PhpParser\Node\NodeFactory;
use Rector\NodeTypeResolver\PHPStan\Type\TypeFactory;
use Rector\SOLID\Rector\Class_\MultiParentingToAbstractDependencyRector;

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
        PhpDocInfoFactory $phpDocInfoFactory,
        PropertyNaming $propertyNaming,
        ClassNaming $classNaming,
        TypeFactory $typeFactory,
        NodeFactory $nodeFactory
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

        $methodBuilder = new Method('inject' . $shortClassName);
        $methodBuilder->makePublic();

        foreach ($objectTypes as $objectType) {
            /** @var ObjectType $objectType */
            $propertyName = $this->propertyNaming->fqnToVariableName($objectType);

            $param = new Param($propertyName);
            $param->setType(new FullyQualified($objectType->getClassName()));
            $methodBuilder->addParam($param);

            $assign = $this->nodeFactory->createPropertyAssignment($propertyName);

            $methodBuilder->addStmt($assign);
        }

        $classMethod = $methodBuilder->getNode();

        if ($framework === MultiParentingToAbstractDependencyRector::FRAMEWORK_SYMFONY) {
            $phpDocInfo = $this->phpDocInfoFactory->createEmpty($classMethod);
            $phpDocInfo->addBareTag('required');
        }

        return $classMethod;
    }
}
