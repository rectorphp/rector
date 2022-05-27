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
use RectorPrefix20220527\Symplify\Astral\ValueObject\NodeBuilder\MethodBuilder;
use RectorPrefix20220527\Symplify\Astral\ValueObject\NodeBuilder\ParamBuilder;
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
