<?php

declare(strict_types=1);

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
 * @see \Rector\Naming\Tests\Rector\Property\MakeBoolPropertyRespectIsHasWasMethodNamingRector\MakeBoolPropertyRespectIsHasWasMethodNamingRectorTest
 * @see \Rector\Naming\Tests\Rector\Property\MakeBoolPropertyRespectIsHasWasMethodNamingRector\Php74Test
 */
final class MakeBoolPropertyRespectIsHasWasMethodNamingRector extends AbstractRector
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

    public function __construct(
        BoolPropertyRenamer $boolPropertyRenamer,
        PropertyRenameFactory $propertyRenameFactory,
        BoolPropertyExpectedNameResolver $boolPropertyExpectedNameResolver
    ) {
        $this->propertyRenameFactory = $propertyRenameFactory;
        $this->boolPropertyRenamer = $boolPropertyRenamer;
        $this->boolPropertyExpectedNameResolver = $boolPropertyExpectedNameResolver;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Renames property to respect is/has/was method naming',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
class SomeClass
{
    private $full = false;

    public function isFull()
    {
        return $this->full;
    }
}
CODE_SAMPLE

                    ,
                    <<<'CODE_SAMPLE'
class SomeClass
{
    private $isFull = false;

    public function isFull()
    {
        return $this->isFull;
    }

}
CODE_SAMPLE

                ),

            ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [Property::class];
    }

    /**
     * @param Property $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $this->nodeTypeResolver->isPropertyBoolean($node)) {
            return null;
        }

        $propertyRename = $this->propertyRenameFactory->create($node, $this->boolPropertyExpectedNameResolver);
        if (! $propertyRename instanceof PropertyRename) {
            return null;
        }

        $property = $this->boolPropertyRenamer->rename($propertyRename);
        if (! $property instanceof \PhpParser\Node\Stmt\Property) {
            return null;
        }

        return $node;
    }
}
