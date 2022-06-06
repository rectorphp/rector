<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Ssch\TYPO3Rector\NodeFactory;

use RectorPrefix20220606\PhpParser\Node;
use RectorPrefix20220606\PhpParser\Node\Expr\Assign;
use RectorPrefix20220606\PhpParser\Node\Expr\PropertyFetch;
use RectorPrefix20220606\PhpParser\Node\Expr\Variable;
use RectorPrefix20220606\PhpParser\Node\Name\FullyQualified;
use RectorPrefix20220606\PhpParser\Node\Param;
use RectorPrefix20220606\PhpParser\Node\Stmt\Class_;
use RectorPrefix20220606\PhpParser\Node\Stmt\ClassMethod;
use RectorPrefix20220606\PhpParser\Node\Stmt\Nop;
use RectorPrefix20220606\PhpParser\Node\Stmt\Property;
use RectorPrefix20220606\PHPStan\Type\ObjectType;
use RectorPrefix20220606\Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use RectorPrefix20220606\Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTagRemover;
use RectorPrefix20220606\Rector\NodeNameResolver\NodeNameResolver;
use RectorPrefix20220606\Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType;
use RectorPrefix20220606\Rector\StaticTypeMapper\ValueObject\Type\ShortenedObjectType;
use RectorPrefix20220606\Symplify\Astral\ValueObject\NodeBuilder\MethodBuilder;
use RectorPrefix20220606\Symplify\Astral\ValueObject\NodeBuilder\ParamBuilder;
final class InjectMethodFactory
{
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTagRemover
     */
    private $phpDocTagRemover;
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory
     */
    private $phpDocInfoFactory;
    public function __construct(NodeNameResolver $nodeNameResolver, PhpDocTagRemover $phpDocTagRemover, PhpDocInfoFactory $phpDocInfoFactory)
    {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->phpDocTagRemover = $phpDocTagRemover;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
    }
    /**
     * @return Node\Stmt[]
     */
    public function createInjectMethodStatements(Class_ $class, Property $property, string $oldAnnotation) : array
    {
        $propertyPhpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($property);
        $statements = [];
        /** @var string $variableName */
        $variableName = $this->nodeNameResolver->getName($property);
        $paramBuilder = new ParamBuilder($variableName);
        $varType = $propertyPhpDocInfo->getVarType();
        if (!$varType instanceof ObjectType) {
            return $statements;
        }
        // Remove the old annotation and use setterInjection instead
        $this->phpDocTagRemover->removeByName($propertyPhpDocInfo, $oldAnnotation);
        if ($varType instanceof FullyQualifiedObjectType) {
            $paramBuilder->setType(new FullyQualified($varType->getClassName()));
        } elseif ($varType instanceof ShortenedObjectType) {
            $paramBuilder->setType($varType->getShortName());
        }
        $param = $paramBuilder->getNode();
        $propertyFetch = new PropertyFetch(new Variable('this'), $variableName);
        $assign = new Assign($propertyFetch, new Variable($variableName));
        // Add new line and then the method
        $statements[] = new Nop();
        $methodAlreadyExists = $class->getMethod($this->createInjectMethodName($variableName));
        if (!$methodAlreadyExists instanceof ClassMethod) {
            $statements[] = $this->createInjectClassMethod($variableName, $param, $assign);
        }
        return $statements;
    }
    private function createInjectClassMethod(string $variableName, Param $param, Assign $assign) : ClassMethod
    {
        $injectMethodName = $this->createInjectMethodName($variableName);
        $injectMethodBuilder = new MethodBuilder($injectMethodName);
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
