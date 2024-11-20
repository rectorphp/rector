<?php

declare (strict_types=1);
namespace Rector\CodingStyle\Rector\Foreach_;

use PhpParser\Node;
use PhpParser\Node\ArrayItem;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Foreach_;
use PhpParser\NodeFinder;
use PhpParser\NodeVisitor;
use Rector\Rector\AbstractRector;
use Rector\ValueObject\PhpVersionFeature;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://wiki.php.net/rfc/short_list_syntax https://www.php.net/manual/en/migration71.new-features.php#migration71.new-features.symmetric-array-destructuring
 *
 * @see \Rector\Tests\CodingStyle\Rector\Foreach_\MultiDimensionalArrayToArrayDestructRector\MultiDimensionalArrayToArrayDestructRectorTest
 */
final class MultiDimensionalArrayToArrayDestructRector extends AbstractRector implements MinPhpVersionInterface
{
    /**
     * @readonly
     */
    private NodeFinder $nodeFinder;
    public function __construct(NodeFinder $nodeFinder)
    {
        $this->nodeFinder = $nodeFinder;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Change multidimensional array access in foreach to array destruct', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    /**
     * @param array<int, array{id: int, name: string}> $users
     */
    public function run(array $users)
    {
        foreach ($users as $user) {
            echo $user['id'];
            echo sprintf('Name: %s', $user['name']);
        }
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    /**
     * @param array<int, array{id: int, name: string}> $users
     */
    public function run(array $users)
    {
        foreach ($users as ['id' => $id, 'name' => $name]) {
            echo $id;
            echo sprintf('Name: %s', $name);
        }
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [Foreach_::class];
    }
    /**
     * @param Foreach_ $node
     */
    public function refactor(Node $node) : ?Node
    {
        $usedDestructedValues = $this->replaceValueArrayAccessorsInForeachTree($node);
        if ($usedDestructedValues !== []) {
            $node->valueVar = new Array_($this->getArrayItems($usedDestructedValues));
            return $node;
        }
        return null;
    }
    public function provideMinPhpVersion() : int
    {
        return PhpVersionFeature::ARRAY_DESTRUCT;
    }
    /**
     * Go through the foreach tree and replace array accessors on "foreach variable"
     * with variables which will be created for array destructor.
     *
     * @return array<string, string> List of destructor variables we need to create in format array key name => variable name
     */
    private function replaceValueArrayAccessorsInForeachTree(Foreach_ $foreach) : array
    {
        $usedVariableNames = $this->getUsedVariableNamesInForeachTree($foreach);
        $createdDestructedVariables = [];
        $this->traverseNodesWithCallable($foreach->stmts, function (Node $traverseNode) use($foreach, $usedVariableNames, &$createdDestructedVariables) {
            if (!$traverseNode instanceof ArrayDimFetch) {
                return null;
            }
            if ($this->nodeComparator->areNodesEqual($traverseNode->var, $foreach->valueVar) === \false) {
                return null;
            }
            $dim = $traverseNode->dim;
            if (!$dim instanceof String_) {
                $createdDestructedVariables = [];
                return NodeVisitor::STOP_TRAVERSAL;
            }
            $destructedVariable = $this->getDestructedVariableName($usedVariableNames, $dim);
            $createdDestructedVariables[$dim->value] = $destructedVariable;
            return new Variable($destructedVariable);
        });
        return $createdDestructedVariables;
    }
    /**
     * Get all variable names which are used in the foreach tree. We need this so that we don't create array destructor
     * with variable name which is already used somewhere bellow
     *
     * @return string[]
     */
    private function getUsedVariableNamesInForeachTree(Foreach_ $foreach) : array
    {
        /** @var list<Variable> $variableNodes */
        $variableNodes = $this->nodeFinder->findInstanceOf($foreach, Variable::class);
        return \array_unique(\array_map(fn(Variable $variable): string => (string) $this->getName($variable), $variableNodes));
    }
    /**
     * Get variable name that will be used for destructor syntax. If variable name is already occupied
     * it will find the first name available by adding numbers after the variable name
     *
     * @param list<string> $usedVariableNames
     */
    private function getDestructedVariableName(array $usedVariableNames, String_ $string) : string
    {
        $desiredVariableName = $string->value;
        if (\in_array($desiredVariableName, $usedVariableNames, \true) === \false) {
            return $desiredVariableName;
        }
        $i = 1;
        $variableName = \sprintf('%s%s', $desiredVariableName, $i);
        while (\in_array($variableName, $usedVariableNames, \true)) {
            ++$i;
            $variableName = \sprintf('%s%s', $desiredVariableName, $i);
        }
        return $variableName;
    }
    /**
     * Convert key-value pairs to ArrayItem instances
     *
     * @param array<string, string> $usedDestructedValues
     *
     * @return list<ArrayItem>
     */
    private function getArrayItems(array $usedDestructedValues) : array
    {
        $items = [];
        foreach ($usedDestructedValues as $key => $value) {
            $items[] = new ArrayItem(new Variable($value), new String_($key));
        }
        return $items;
    }
}
