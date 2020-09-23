<?php

declare(strict_types=1);

namespace Rector\Naming\Rector\Property;

use PhpParser\Node;
use PhpParser\Node\Stmt\Property;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\Naming\ConflictingNameResolver\PropertyConflictingNameResolver;
use Rector\Naming\ExpectedNameResolver\FromPropertyTypeExpectedNameResolver;
use Rector\Naming\PropertyRenamer;
use Rector\Naming\ValueObjectFactory\PropertyRenameFactory;

/**
 * @see \Rector\Naming\Tests\Rector\Property\MakeBoolPropertyRespectIsHasWasMethodNamingRector\MakeBoolPropertyRespectIsHasWasMethodNamingRectorTest
 * @see \Rector\Naming\Tests\Rector\Property\MakeBoolPropertyRespectIsHasWasMethodNamingRector\Php74Test
 */
final class MakeBoolPropertyRespectIsHasWasMethodNamingRector extends AbstractRector
{
    /**
     * @var PropertyRenamer
     */
    private $propertyRenamer;

    /**
     * @var PropertyRenameFactory
     */
    private $propertyRenameFactory;

    public function __construct(
        PropertyRenamer $propertyRenamer,
        FromPropertyTypeExpectedNameResolver $fromPropertyTypeExpectedNameResolver,
        PropertyConflictingNameResolver $propertyConflictingNameResolver,
        PropertyRenameFactory $propertyRenameFactory
    ) {
        $propertyConflictingNameResolver->setExpectedNameResolver($fromPropertyTypeExpectedNameResolver);

        $this->propertyRenamer = $propertyRenamer;
        $this->propertyRenamer->setConflictingNameResolver($propertyConflictingNameResolver);

        $this->propertyRenameFactory = $propertyRenameFactory;
        $this->propertyRenameFactory->setExpectedNameResolver($fromPropertyTypeExpectedNameResolver);
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Renames property to respect is/has/was method naming', [
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
        if (! $this->isPropertyBoolean($node)) {
            return null;
        }

        $propertyRename = $this->propertyRenameFactory->create($node);
        if ($propertyRename === null) {
            return null;
        }

        if ($this->propertyRenamer->rename($propertyRename) === null) {
            return null;
        }

        return $node;
    }
}
