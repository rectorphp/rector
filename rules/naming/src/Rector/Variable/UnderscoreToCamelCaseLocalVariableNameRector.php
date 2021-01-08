<?php

declare(strict_types=1);

namespace Rector\Naming\Rector\Variable;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use Rector\Core\Php\ReservedKeywordAnalyzer;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\Util\StaticRectorStrings;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\Naming\Tests\Rector\Variable\UnderscoreToCamelCaseLocalVariableNameRector\UnderscoreToCamelCaseLocalVariableNameRectorTest
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

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Change under_score local variable names to camelCase',
            [
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
        if ($this->isReserved($camelCaseName)) {
            return null;
        }

        $parentNode = $node->getAttribute(AttributeKey::PARENT_NODE);
        if ($parentNode instanceof Expr && $this->isFoundInParentNode($node)) {
            return null;
        }

        if (($parentNode instanceof Arg || $parentNode instanceof Param || $parentNode instanceof Stmt)
            && $this->isFoundInParentNode($node)) {
            return null;
        }

        if ($this->isFoundInPreviousNode($node)) {
            return null;
        }

        $node->name = $camelCaseName;

        return $node;
    }

    private function isReserved(string $string): bool
    {
        if ($string === 'this') {
            return true;
        }

        if ($string === '') {
            return true;
        }

        return is_numeric($string[0]);
    }

    private function isFoundInParentNode(Variable $variable): bool
    {
        /** @var ClassMethod|Function_|null $classMethodOrFunction */
        $classMethodOrFunction = $this->betterNodeFinder->findParentTypes(
            $variable,
            [ClassMethod::class, Function_::class]
        );

        if ($classMethodOrFunction === null) {
            return false;
        }

        /** @var Param[] $params */
        $params = $classMethodOrFunction->getParams();

        foreach ($params as $param) {
            if ($this->areNamesEqual($param->var, $variable)) {
                return true;
            }
        }

        return false;
    }

    private function isFoundInPreviousNode(Variable $variable): bool
    {
        $previousNode = $variable->getAttribute(AttributeKey::PREVIOUS_NODE);
        if (! $previousNode instanceof Expr) {
            return false;
        }
        return $this->isFoundInParentNode($variable);
    }
}
