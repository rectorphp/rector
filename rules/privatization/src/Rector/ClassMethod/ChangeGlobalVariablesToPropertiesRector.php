<?php

declare(strict_types=1);

namespace Rector\Privatization\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Global_;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see https://3v4l.org/DWC4P
 *
 * @see https://stackoverflow.com/a/12446305/1348344
 * @see \Rector\Privatization\Tests\Rector\ClassMethod\ChangeGlobalVariablesToPropertiesRector\ChangeGlobalVariablesToPropertiesRectorTest
 */
final class ChangeGlobalVariablesToPropertiesRector extends AbstractRector
{
    /**
     * @var string[]
     */
    private $globalVariableNames = [];

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Change global $variables to private properties',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
class SomeClass
{
    public function go()
    {
        global $variable;
        $variable = 5;
    }

    public function run()
    {
        global $variable;
        var_dump($variable);
    }
}
CODE_SAMPLE
,
                    <<<'CODE_SAMPLE'
class SomeClass
{
    private $variable;
    public function go()
    {
        $this->variable = 5;
    }

    public function run()
    {
        var_dump($this->variable);
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
        return [ClassMethod::class];
    }

    /**
     * @param ClassMethod $node
     */
    public function refactor(Node $node): ?Node
    {
        $classLike = $node->getAttribute(AttributeKey::CLASS_NODE);
        if (! $classLike instanceof Class_) {
            return null;
        }

        $this->collectGlobalVariableNamesAndRefactorToPropertyFetch($node);

        if ($this->globalVariableNames === []) {
            return null;
        }

        foreach ($this->globalVariableNames as $globalVariableName) {
            $this->addPropertyToClass($classLike, null, $globalVariableName);
        }

        return $node;
    }

    private function collectGlobalVariableNamesAndRefactorToPropertyFetch(ClassMethod $classMethod): void
    {
        $this->globalVariableNames = [];

        $this->traverseNodesWithCallable($classMethod, function (Node $node): ?PropertyFetch {
            if ($node instanceof Global_) {
                $this->refactorGlobal($node);
                return null;
            }

            if ($node instanceof Variable) {
                return $this->refactorGlobalVariable($node);
            }

            return null;
        });
    }

    private function refactorGlobal(Global_ $global): void
    {
        foreach ($global->vars as $var) {
            $varName = $this->getName($var);
            if ($varName === null) {
                return;
            }

            $this->globalVariableNames[] = $varName;
        }

        $this->removeNode($global);
    }

    private function refactorGlobalVariable(Variable $variable): ?PropertyFetch
    {
        if (! $this->isNames($variable, $this->globalVariableNames)) {
            return null;
        }

        // replace with property fetch
        $variableName = $this->getName($variable);
        if ($variableName === null) {
            return null;
        }

        return $this->nodeFactory->createPropertyFetch('this', $variableName);
    }
}
