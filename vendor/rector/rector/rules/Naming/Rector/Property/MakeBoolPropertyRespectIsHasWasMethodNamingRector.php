<?php

declare (strict_types=1);
namespace Rector\Naming\Rector\Property;

use PhpParser\Node;
use PhpParser\Node\Stmt\Property;
use Rector\Core\Rector\AbstractRector;
use Rector\Naming\ExpectedNameResolver\BoolPropertyExpectedNameResolver;
use Rector\Naming\PropertyRenamer\BoolPropertyRenamer;
use Rector\Naming\ValueObject\PropertyRename;
use Rector\Naming\ValueObjectFactory\PropertyRenameFactory;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\Naming\Rector\Property\MakeBoolPropertyRespectIsHasWasMethodNamingRector\MakeBoolPropertyRespectIsHasWasMethodNamingRectorTest
 * @see \Rector\Tests\Naming\Rector\Property\MakeBoolPropertyRespectIsHasWasMethodNamingRector\Php74Test
 */
final class MakeBoolPropertyRespectIsHasWasMethodNamingRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @var PropertyRenameFactory
     */
    private $propertyRenameFactory;
    /**
     * @var BoolPropertyRenamer
     */
    private $boolPropertyRenamer;
    /**
     * @var BoolPropertyExpectedNameResolver
     */
    private $boolPropertyExpectedNameResolver;
    public function __construct(\Rector\Naming\PropertyRenamer\BoolPropertyRenamer $boolPropertyRenamer, \Rector\Naming\ValueObjectFactory\PropertyRenameFactory $propertyRenameFactory, \Rector\Naming\ExpectedNameResolver\BoolPropertyExpectedNameResolver $boolPropertyExpectedNameResolver)
    {
        $this->propertyRenameFactory = $propertyRenameFactory;
        $this->boolPropertyRenamer = $boolPropertyRenamer;
        $this->boolPropertyExpectedNameResolver = $boolPropertyExpectedNameResolver;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Renames property to respect is/has/was method naming', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
private $full = false;

public function isFull()
{
    return $this->full;
}
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
private $isFull = false;

public function isFull()
{
    return $this->isFull;
}

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
        if (!$this->nodeTypeResolver->isPropertyBoolean($node)) {
            return null;
        }
        $expectedBoolName = $this->boolPropertyExpectedNameResolver->resolve($node);
        if ($expectedBoolName === null) {
            return null;
        }
        $propertyRename = $this->propertyRenameFactory->createFromExpectedName($node, $expectedBoolName);
        if (!$propertyRename instanceof \Rector\Naming\ValueObject\PropertyRename) {
            return null;
        }
        $property = $this->boolPropertyRenamer->rename($propertyRename);
        if (!$property instanceof \PhpParser\Node\Stmt\Property) {
            return null;
        }
        return $node;
    }
}
