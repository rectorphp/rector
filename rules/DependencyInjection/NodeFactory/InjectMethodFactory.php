<?php

declare (strict_types=1);
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
use RectorPrefix20211020\Symplify\Astral\ValueObject\NodeBuilder\MethodBuilder;
use RectorPrefix20211020\Symplify\Astral\ValueObject\NodeBuilder\ParamBuilder;
final class InjectMethodFactory
{
    /**
     * @var \Rector\CodingStyle\Naming\ClassNaming
     */
    private $classNaming;
    /**
     * @var \Rector\Core\PhpParser\Node\NodeFactory
     */
    private $nodeFactory;
    /**
     * @var \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory
     */
    private $phpDocInfoFactory;
    /**
     * @var \Rector\Naming\Naming\PropertyNaming
     */
    private $propertyNaming;
    /**
     * @var \Rector\NodeTypeResolver\PHPStan\Type\TypeFactory
     */
    private $typeFactory;
    public function __construct(\Rector\CodingStyle\Naming\ClassNaming $classNaming, \Rector\Core\PhpParser\Node\NodeFactory $nodeFactory, \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory $phpDocInfoFactory, \Rector\Naming\Naming\PropertyNaming $propertyNaming, \Rector\NodeTypeResolver\PHPStan\Type\TypeFactory $typeFactory)
    {
        $this->classNaming = $classNaming;
        $this->nodeFactory = $nodeFactory;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->propertyNaming = $propertyNaming;
        $this->typeFactory = $typeFactory;
    }
    /**
     * @param ObjectType[] $objectTypes
     */
    public function createFromTypes(array $objectTypes, string $className, string $framework) : \PhpParser\Node\Stmt\ClassMethod
    {
        $objectTypes = $this->typeFactory->uniquateTypes($objectTypes);
        $shortClassName = $this->classNaming->getShortName($className);
        $methodBuilder = new \RectorPrefix20211020\Symplify\Astral\ValueObject\NodeBuilder\MethodBuilder('inject' . $shortClassName);
        $methodBuilder->makePublic();
        foreach ($objectTypes as $objectType) {
            /** @var ObjectType $objectType */
            $propertyName = $this->propertyNaming->fqnToVariableName($objectType);
            $paramBuilder = new \RectorPrefix20211020\Symplify\Astral\ValueObject\NodeBuilder\ParamBuilder($propertyName);
            $paramBuilder->setType(new \PhpParser\Node\Name\FullyQualified($objectType->getClassName()));
            $methodBuilder->addParam($paramBuilder);
            $assign = $this->nodeFactory->createPropertyAssignment($propertyName);
            $methodBuilder->addStmt($assign);
        }
        $classMethod = $methodBuilder->getNode();
        if ($framework === \Rector\Core\ValueObject\FrameworkName::SYMFONY) {
            $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($classMethod);
            $phpDocInfo->addPhpDocTagNode(new \PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode('@required', new \PHPStan\PhpDocParser\Ast\PhpDoc\GenericTagValueNode('')));
        }
        return $classMethod;
    }
}
