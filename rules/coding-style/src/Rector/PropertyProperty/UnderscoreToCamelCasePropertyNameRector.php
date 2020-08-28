<?php

declare(strict_types=1);

namespace Rector\CodingStyle\Rector\PropertyProperty;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticPropertyFetch;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\PropertyProperty;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\Core\Util\StaticRectorStrings;
use Rector\NodeTypeResolver\Node\AttributeKey;

/**
 * @see \Rector\CodingStyle\Tests\Rector\PropertyProperty\UnderscoreToCamelCasePropertyNameRector\UnderscoreToCamelCasePropertyNameRectorTest
 */
final class UnderscoreToCamelCasePropertyNameRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Change under_score names to camelCase', [
            new CodeSample(
                <<<'PHP'
final class SomeClass
{
    public $property_name;

    public function run($a)
    {
        $this->property_name = 5;
    }
}
PHP
,
                <<<'PHP'
final class SomeClass
{
    public $propertyName;

    public function run($a)
    {
        $this->propertyName = 5;
    }
}
PHP
            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [PropertyProperty::class, PropertyFetch::class, StaticPropertyFetch::class];
    }

    /**
     * @param PropertyProperty|PropertyFetch|StaticPropertyFetch $node
     */
    public function refactor(Node $node): ?Node
    {
        $nodeName = $this->getName($node);
        if ($nodeName === null) {
            return null;
        }

        /** @var string $class */
        $class = $node->getAttribute(AttributeKey::CLASS_NAME);
        // properties are accessed via magic, nothing we can do
        if (method_exists($class, '__set') || method_exists($class, '__get')) {
            return null;
        }

        if (! Strings::contains($nodeName, '_')) {
            return null;
        }

        $camelCaseName = $this->createCamelName($nodeName, $node);

        $node->name = new Identifier($camelCaseName);

        return $node;
    }

    /**
     * @param PropertyProperty|PropertyFetch|StaticPropertyFetch $node
     */
    private function createCamelName(string $nodeName, Node $node): string
    {
        $camelCaseName = StaticRectorStrings::underscoreToCamelCase($nodeName);

        if ($node instanceof StaticPropertyFetch || $node instanceof PropertyProperty) {
            $camelCaseName = '$' . $camelCaseName;
        }

        return $camelCaseName;
    }
}
