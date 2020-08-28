<?php

declare(strict_types=1);

namespace Rector\CodingStyle\Rector\Variable;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Expr\Variable;
use Rector\Core\Php\ReservedKeywordAnalyzer;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\Core\Util\StaticRectorStrings;

/**
 * @see \Rector\CodingStyle\Tests\Rector\Variable\UnderscoreToCamelCaseVariableNameRector\UnderscoreToCamelCaseVariableNameRectorTest
 */
final class UnderscoreToCamelCaseVariableNameRector extends AbstractRector
{
    /**
     * @var ReservedKeywordAnalyzer
     */
    private $reservedKeywordAnalyzer;

    public function __construct(ReservedKeywordAnalyzer $reservedKeywordAnalyzer)
    {
        $this->reservedKeywordAnalyzer = $reservedKeywordAnalyzer;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Change under_score names to camelCase', [
            new CodeSample(
                <<<'PHP'
final class SomeClass
{
    public function run($a_b)
    {
        $some_value = $a_b;
    }
}
PHP
,
                <<<'PHP'
final class SomeClass
{
    public function run($aB)
    {
        $someValue = $aB;
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
        return [Variable::class];
    }

    /**
     * @param Variable $node
     */
    public function refactor(Node $node): ?Node
    {
        $nodeName = $this->getName($node);
        if ($nodeName === null) {
            return null;
        }

        if (! Strings::contains($nodeName, '_')) {
            return null;
        }

        if ($this->reservedKeywordAnalyzer->isNativeVariable($nodeName)) {
            return null;
        }

        $camelCaseName = StaticRectorStrings::underscoreToCamelCase($nodeName);
        if ($camelCaseName === 'this') {
            return null;
        }

        $node->name = $camelCaseName;

        return $node;
    }
}
