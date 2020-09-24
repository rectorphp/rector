<?php

declare(strict_types=1);

namespace Rector\CodingStyle\Rector\Variable;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
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

        $parentNode = $node->getAttribute(AttributeKey::PARENT_NODE);
        if (($parentNode instanceof Arg || $parentNode instanceof Param) && $this->isFoundInParentNode($node)) {
            return null;
        }

        $previousNode = $node->getAttribute(AttributeKey::PREVIOUS_NODE);
        if ($previousNode instanceof Variable && $this->isFoundInParentNode($node)) {
            return null;
        }

        $node->name = $camelCaseName;

        return $node;
    }

    /**
     * @return mixed
     */
    private function isFoundInParentNode(Variable $node): bool
    {
        $parentNode = $node->getAttribute(AttributeKey::PARENT_NODE);
        while (! $parentNode instanceof ClassMethod || ! $parentNode instanceof Function_) {
            if ($parentNode === null) {
                break;
            }

            $parentNode = $parentNode->getAttribute(AttributeKey::PARENT_NODE);
            if ($parentNode instanceof ClassMethod || $parentNode instanceof Function_) {
                break;
            }
        }

        if ($parentNode === null) {
            return false;
        }

        $params = $parentNode->getParams();
        foreach ($params as $param) {
            if ($param->var->name === $node->name) {
                return true;
            }
        }

        return false;
    }
}
