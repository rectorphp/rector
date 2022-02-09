<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\NodeFactory;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Nop;
use PhpParser\Node\Stmt\Property;
use PHPStan\Type\ObjectType;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTagRemover;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType;
use Rector\StaticTypeMapper\ValueObject\Type\ShortenedObjectType;
use RectorPrefix20220209\Symplify\Astral\ValueObject\NodeBuilder\MethodBuilder;
use RectorPrefix20220209\Symplify\Astral\ValueObject\NodeBuilder\ParamBuilder;
final class InjectMethodFactory
{
    /**
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @var \Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTagRemover
     */
    private $phpDocTagRemover;
    /**
     * @var \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory
     */
    private $phpDocInfoFactory;
    public function __construct(\Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver, \Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTagRemover $phpDocTagRemover, \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory $phpDocInfoFactory)
    {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->phpDocTagRemover = $phpDocTagRemover;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
    }
    /**
     * @return Node\Stmt[]
     */
    public function createInjectMethodStatements(\PhpParser\Node\Stmt\Class_ $class, \PhpParser\Node\Stmt\Property $property, string $oldAnnotation) : array
    {
        $propertyPhpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($property);
        $statements = [];
        /** @var string $variableName */
        $variableName = $this->nodeNameResolver->getName($property);
        $paramBuilder = new \RectorPrefix20220209\Symplify\Astral\ValueObject\NodeBuilder\ParamBuilder($variableName);
        $varType = $propertyPhpDocInfo->getVarType();
        if (!$varType instanceof \PHPStan\Type\ObjectType) {
            return $statements;
        }
        // Remove the old annotation and use setterInjection instead
        $this->phpDocTagRemover->removeByName($propertyPhpDocInfo, $oldAnnotation);
        if ($varType instanceof \Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType) {
            $paramBuilder->setType(new \PhpParser\Node\Name\FullyQualified($varType->getClassName()));
        } elseif ($varType instanceof \Rector\StaticTypeMapper\ValueObject\Type\ShortenedObjectType) {
            $paramBuilder->setType($varType->getShortName());
        }
        $param = $paramBuilder->getNode();
        $propertyFetch = new \PhpParser\Node\Expr\PropertyFetch(new \PhpParser\Node\Expr\Variable('this'), $variableName);
        $assign = new \PhpParser\Node\Expr\Assign($propertyFetch, new \PhpParser\Node\Expr\Variable($variableName));
        // Add new line and then the method
        $statements[] = new \PhpParser\Node\Stmt\Nop();
        $methodAlreadyExists = $class->getMethod($this->createInjectMethodName($variableName));
        if (!$methodAlreadyExists instanceof \PhpParser\Node\Stmt\ClassMethod) {
            $statements[] = $this->createInjectClassMethod($variableName, $param, $assign);
        }
        return $statements;
    }
    private function createInjectClassMethod(string $variableName, \PhpParser\Node\Param $param, \PhpParser\Node\Expr\Assign $assign) : \PhpParser\Node\Stmt\ClassMethod
    {
        $injectMethodName = $this->createInjectMethodName($variableName);
        $injectMethodBuilder = new \RectorPrefix20220209\Symplify\Astral\ValueObject\NodeBuilder\MethodBuilder($injectMethodName);
        $injectMethodBuilder->makePublic();
        $injectMethodBuilder->addParam($param);
        $injectMethodBuilder->setReturnType('void');
        $injectMethodBuilder->addStmt($assign);
        return $injectMethodBuilder->getNode();
    }
    private function createInjectMethodName(string $variableName) : string
    {
        return 'inject' . \ucfirst($variableName);
    }
}
