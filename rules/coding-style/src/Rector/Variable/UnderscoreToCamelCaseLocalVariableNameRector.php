<?php

declare(strict_types=1);

namespace Rector\CodingStyle\Rector\Variable;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Param;
use Rector\Core\Php\ReservedKeywordAnalyzer;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\Core\Util\StaticRectorStrings;
use Rector\NodeTypeResolver\Node\AttributeKey;

/**
 * @see \Rector\CodingStyle\Tests\Rector\Variable\UnderscoreToCamelCaseLocalVariableNameRector\UnderscoreToCamelCaseLocalVariableNameRectorTest
 */
final class UnderscoreToCamelCaseLocalVariableNameRector extends AbstractRector
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
        return new RectorDefinition('Change under_score local variable names to camelCase', [
            new CodeSample(
                <<<'CODE_SAMPLE'
final class SomeClass
{
    public function run($a_b)
    {
        $some_value = $a_b;
    }
}
CODE_SAMPLE
,
                <<<'CODE_SAMPLE'
final class SomeClass
{
    public function run($a_b)
    {
        $someValue = $a_b;
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
        return [Variable::class];
    }

    /**
     * @param Variable $node
     */
    public function refactor(Node $node): ?Node
    {
        $parentNode = $node->getAttribute(AttributeKey::PARENT_NODE);
        if ($parentNode instanceof Param || $parentNode instanceof Arg) {
            return null;
        }

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
