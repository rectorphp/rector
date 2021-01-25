<?php

declare(strict_types=1);

namespace Rector\Naming\Rector\Property;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Stmt\Property;
use Rector\Core\Rector\AbstractRector;
use Rector\Naming\ExpectedNameResolver\UnderscoreCamelCaseExpectedNameResolver;
use Rector\Naming\PropertyRenamer\UnderscoreCamelCasePropertyRenamer;
use Rector\Naming\ValueObject\PropertyRename;
use Rector\Naming\ValueObjectFactory\PropertyRenameFactory;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\Naming\Tests\Rector\Property\UnderscoreToCamelCasePropertyNameRector\UnderscoreToCamelCasePropertyNameRectorTest
 */
final class UnderscoreToCamelCasePropertyNameRector extends AbstractRector
{
    /**
     * @var PropertyRenameFactory
     */
    private $propertyRenameFactory;

    /**
     * @var UnderscoreCamelCasePropertyRenamer
     */
    private $underscoreCamelCasePropertyRenamer;

    /**
     * @var UnderscoreCamelCaseExpectedNameResolver
     */
    private $underscoreCamelCaseExpectedNameResolver;

    public function __construct(
        UnderscoreCamelCasePropertyRenamer $underscoreCamelCasePropertyRenamer,
        PropertyRenameFactory $propertyRenameFactory,
        UnderscoreCamelCaseExpectedNameResolver $underscoreCamelCaseExpectedNameResolver
    ) {
        $this->underscoreCamelCasePropertyRenamer = $underscoreCamelCasePropertyRenamer;
        $this->propertyRenameFactory = $propertyRenameFactory;
        $this->underscoreCamelCaseExpectedNameResolver = $underscoreCamelCaseExpectedNameResolver;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Change under_score names to camelCase', [
            new CodeSample(
                <<<'CODE_SAMPLE'
final class SomeClass
{
    public $property_name;

    public function run($a)
    {
        $this->property_name = 5;
    }
}
CODE_SAMPLE
,
                <<<'CODE_SAMPLE'
final class SomeClass
{
    public $propertyName;

    public function run($a)
    {
        $this->propertyName = 5;
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
        $propertyName = $this->getName($node);
        if (! Strings::contains($propertyName, '_')) {
            return null;
        }

        $propertyRename = $this->propertyRenameFactory->create($node, $this->underscoreCamelCaseExpectedNameResolver);
        if (! $propertyRename instanceof PropertyRename) {
            return null;
        }
        $property = $this->underscoreCamelCasePropertyRenamer->rename($propertyRename);

        if (! $property instanceof \PhpParser\Node\Stmt\Property) {
            return null;
        }

        return $node;
    }
}
