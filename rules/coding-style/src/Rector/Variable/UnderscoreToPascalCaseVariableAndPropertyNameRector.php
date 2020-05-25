<?php

declare(strict_types=1);

namespace Rector\CodingStyle\Rector\Variable;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticPropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\PropertyProperty;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\Core\Util\StaticRectorStrings;

/**
 * @see \Rector\CodingStyle\Tests\Rector\Variable\UnderscoreToPascalCaseVariableAndPropertyNameRector\UnderscoreToPascalCaseVariableAndPropertyNameRectorTest
 */
final class UnderscoreToPascalCaseVariableAndPropertyNameRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Change under_score names to pascalCase', [
            new CodeSample(
                <<<'PHP'
final class SomeClass
{
    public function run($a_b)
    {
        $some_value = 5;

        $this->run($a_b);
    }
}
PHP
,
                <<<'PHP'
final class SomeClass
{
    public function run($aB)
    {
        $someValue = 5;

        $this->run($aB);
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
        return [Variable::class, PropertyProperty::class, PropertyFetch::class, StaticPropertyFetch::class];
    }

    /**
     * @param Variable|PropertyProperty|PropertyFetch|StaticPropertyFetch $node
     */
    public function refactor(Node $node): ?Node
    {
        $nodeName = $this->getName($node);
        if ($nodeName === null) {
            return null;
        }

        if (Strings::startsWith($nodeName, '_')) {
            return null;
        }

        if (! Strings::contains($nodeName, '_')) {
            return null;
        }

        $camelCaseName = $this->createPascalName($nodeName, $node);
        if ($camelCaseName === 'this') {
            return null;
        }

        $node->name = new Identifier($camelCaseName);

        return $node;
    }

    /**
     * @param Variable|PropertyProperty|PropertyFetch|StaticPropertyFetch $node
     */
    private function createPascalName(string $nodeName, Node $node): string
    {
        $camelCaseName = StaticRectorStrings::underscoreToPascalCase($nodeName);
        if ($node instanceof StaticPropertyFetch || $node instanceof PropertyProperty) {
            return '$' . $camelCaseName;
        }

        return $camelCaseName;
    }
}
