<?php

declare(strict_types=1);

namespace Rector\Naming\Rector\Property;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\Stmt\PropertyProperty;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\Core\Util\StaticRectorStrings;
use Rector\NodeTypeResolver\Node\AttributeKey;

/**
 * @see \Rector\Naming\Tests\Rector\Property\RenamePropertyUnderscoreToCamelCaseRector\RenamePropertyUnderscoreToCamelCaseRectorTest
 */
final class RenamePropertyUnderscoreToCamelCaseRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Renames property with underscore to camel case', [
            new CodeSample(
                <<<'CODE_SAMPLE'
class SomeClass
{
    private $some_property;

    public function run(): void
    {
        $this->some_property;
    }
}
CODE_SAMPLE

                ,
                <<<'CODE_SAMPLE'
class SomeClass
{
    private $someProperty;

    public function run(): void
    {
        $this->someProperty;
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
        return [Property::class, PropertyFetch::class];
    }

    /**
     * @param Property|PropertyFetch $node
     */
    public function refactor(Node $node): ?Node
    {
        /** @var string $propertyName */
        $propertyName = $this->getName($node);
        if (! Strings::contains($propertyName, '_')) {
            return null;
        }

        $propertyName = StaticRectorStrings::underscoreToCamelCase($propertyName);
        if ($node instanceof Property) {
            $node->props = [new PropertyProperty($propertyName)];
            return $node;
        }

        /** @var Node $object */
        $object = $node->var->getAttribute(AttributeKey::ORIGINAL_NODE);
        /** @var string */
        $objectName = $this->getName($object);
        return new PropertyFetch(new Variable($objectName), $propertyName);
    }
}
