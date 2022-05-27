<?php

declare (strict_types=1);
namespace Rector\CodingStyle\Rector\Property;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\Stmt\Trait_;
use PHPStan\Type\BooleanType;
use Rector\Core\Rector\AbstractRector;
use Rector\TypeDeclaration\AlreadyAssignDetector\ConstructorAssignDetector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\CodingStyle\Rector\Property\AddFalseDefaultToBoolPropertyRector\AddFalseDefaultToBoolPropertyRectorTest
 */
final class AddFalseDefaultToBoolPropertyRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @readonly
     * @var \Rector\TypeDeclaration\AlreadyAssignDetector\ConstructorAssignDetector
     */
    private $constructorAssignDetector;
    public function __construct(\Rector\TypeDeclaration\AlreadyAssignDetector\ConstructorAssignDetector $constructorAssignDetector)
    {
        $this->constructorAssignDetector = $constructorAssignDetector;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Add false default to bool properties, to prevent null compare errors', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    /**
     * @var bool
     */
    private $isDisabled;
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    /**
     * @var bool
     */
    private $isDisabled = false;
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Stmt\Property::class];
    }
    /**
     * @param Property $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if (\count($node->props) !== 1) {
            return null;
        }
        $onlyProperty = $node->props[0];
        if ($onlyProperty->default !== null) {
            return null;
        }
        if (!$this->isBoolDocType($node)) {
            return null;
        }
        $classLike = $this->betterNodeFinder->findParentByTypes($node, [\PhpParser\Node\Stmt\Class_::class, \PhpParser\Node\Stmt\Trait_::class]);
        if (!$classLike instanceof \PhpParser\Node\Stmt\ClassLike) {
            return null;
        }
        $propertyName = $this->nodeNameResolver->getName($onlyProperty);
        if ($this->constructorAssignDetector->isPropertyAssigned($classLike, $propertyName)) {
            return null;
        }
        $onlyProperty->default = $this->nodeFactory->createFalse();
        return $node;
    }
    private function isBoolDocType(\PhpParser\Node\Stmt\Property $property) : bool
    {
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($property);
        return $phpDocInfo->getVarType() instanceof \PHPStan\Type\BooleanType;
    }
}
