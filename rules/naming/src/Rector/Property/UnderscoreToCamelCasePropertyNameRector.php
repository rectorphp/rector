<?php

declare(strict_types=1);

namespace Rector\Naming\Rector\Property;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Stmt\Property;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\Naming\ConflictingNameResolver\PropertyConflictingNameResolver;
use Rector\Naming\ExpectedNameResolver\UnderscoreCamelCaseExpectedNameResolver;
use Rector\Naming\PropertyRenamer;
use Rector\Naming\ValueObjectFactory\PropertyRenameFactory;
use Rector\NodeTypeResolver\Node\AttributeKey;

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
     * @var PropertyRenamer
     */
    private $propertyRenamer;

    /**
     * @var UnderscoreCamelCaseExpectedNameResolver
     */
    private $underscoreCamelCaseExpectedNameResolver;

    /**
     * @var PropertyConflictingNameResolver
     */
    private $propertyConflictingNameResolver;

    public function __construct(
        PropertyRenamer $propertyRenamer,
        UnderscoreCamelCaseExpectedNameResolver $underscoreCamelCaseExpectedNameResolver,
        PropertyConflictingNameResolver $propertyConflictingNameResolver,
        PropertyRenameFactory $propertyRenameFactory
    ) {
        $this->propertyRenamer = $propertyRenamer;
        $this->propertyRenameFactory = $propertyRenameFactory;
        $this->underscoreCamelCaseExpectedNameResolver = $underscoreCamelCaseExpectedNameResolver;
        $this->propertyConflictingNameResolver = $propertyConflictingNameResolver;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Change under_score names to camelCase', [
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
        $this->propertyConflictingNameResolver->setExpectedNameResolver($this->underscoreCamelCaseExpectedNameResolver);

        $this->propertyRenameFactory->setExpectedNameResolver($this->underscoreCamelCaseExpectedNameResolver);
        $this->propertyRenamer->setConflictingNameResolver($this->propertyConflictingNameResolver);

        $nodeName = $this->getName($node);
        if ($nodeName === null) {
            return null;
        }

        if (! Strings::contains($nodeName, '_')) {
            return null;
        }

        /** @var string $class */
        $class = $node->getAttribute(AttributeKey::CLASS_NAME);
        // properties are accessed via magic, nothing we can do
        if (method_exists($class, '__set') || method_exists($class, '__get')) {
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
