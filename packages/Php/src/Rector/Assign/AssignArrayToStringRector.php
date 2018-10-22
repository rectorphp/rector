<?php declare(strict_types=1);

namespace Rector\Php\Rector\Assign;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Cast\Array_ as ArrayCast;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticPropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\PropertyProperty;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;
use Rector\Utils\BetterNodeFinder;
use Rector\Utils\NodeTraverser\CallableNodeTraverser;

/**
 * This depends on the context. We need more real app datas.
 *
 * @see https://3v4l.org/ABDNv
 * @see https://stackoverflow.com/a/41000866/1348344
 */
final class AssignArrayToStringRector extends AbstractRector
{
    /**
     * @var CallableNodeTraverser
     */
    private $callableNodeTraverser;

    /**
     * @var PropertyProperty[]
     */
    private $emptyStringPropertyNodes = [];

    /**
     * @var BetterNodeFinder
     */
    private $betterNodeFinder;

    public function __construct(CallableNodeTraverser $callableNodeTraverser, BetterNodeFinder $betterNodeFinder)
    {
        $this->callableNodeTraverser = $callableNodeTraverser;
        $this->betterNodeFinder = $betterNodeFinder;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'String cannot be turned into array by assignment anymore',
            [new CodeSample(
<<<'CODE_SAMPLE'
$string = '';
$string[] = 1;
CODE_SAMPLE
                ,
<<<'CODE_SAMPLE'
$string = [];
$string[] = 1;
CODE_SAMPLE
            )]
        );
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [Assign::class];
    }

    /**
     * @param Assign $node
     */
    public function refactor(Node $node): ?Node
    {
        // only array with no explicit key assign, e.g. "$value[] = 5";
        if (! $node->var instanceof ArrayDimFetch || $node->var->dim !== null) {
            return null;
        }

        $arrayDimFetchNode = $node->var;

        /** @var Variable|PropertyFetch|StaticPropertyFetch|Expr $variableNode */
        $variableNode = $arrayDimFetchNode->var;

        // set default value to property
        if ($variableNode instanceof PropertyFetch || $variableNode instanceof StaticPropertyFetch) {
            if ($this->processProperty($variableNode)) {
                return $node;
            }
        }

        // fallback to variable, property or static property = '' set
        if ($this->processVariable($node, $variableNode)) {
            return $node;
        }

        // there is "$string[] = ...;", which would cause error in PHP 7+
        // fallback - if no array init found, retype to (array)
        $retypeArrayAssignNode = new Assign($arrayDimFetchNode->var, new ArrayCast($arrayDimFetchNode->var));

        $this->addNodeAfterNode(clone $node, $node);

        return $retypeArrayAssignNode;
    }

    /**
     * @param Node[] $nodes
     */
    public function beforeTraverse(array $nodes): void
    {
        // collect all known "{anything} = '';" assigns

        $this->callableNodeTraverser->traverseNodesWithCallable($nodes, function (Node $node): void {
            if ($node instanceof PropertyProperty && $node->default && $this->isEmptyStringNode($node->default)) {
                $this->emptyStringPropertyNodes[] = $node;
            }
        });
    }

    private function isEmptyStringNode(Node $node): bool
    {
        return $node instanceof String_ && $node->value === '';
    }

    /**
     * @param Variable|PropertyFetch|StaticPropertyFetch|Expr $variableNode
     */
    private function processVariable(Assign $assignNode, Expr $variableNode): bool
    {
        if (! $this->isStringType($variableNode)) {
            return false;
        }

        $variableNodeContent = $this->print($variableNode);

        $variableAssign = $this->betterNodeFinder->findFirstPrevious($assignNode, function (Node $node) use (
            $variableNodeContent
        ) {
            if (! $node instanceof Assign) {
                return false;
            }

            if ($this->print($node->var) !== $variableNodeContent) {
                return false;
            }

            // we look for variable assign = string
            if (! $this->isEmptyStringNode($node->expr)) {
                return false;
            }

            return true;
        });

        if ($variableAssign instanceof Assign) {
            $variableAssign->expr = new Array_();
            return true;
        }

        return false;
    }

    /**
     * @param PropertyFetch|StaticPropertyFetch $propertyNode
     */
    private function processProperty(Node $propertyNode): bool
    {
        foreach ($this->emptyStringPropertyNodes as $emptyStringPropertyNode) {
            if ($this->getName($emptyStringPropertyNode) === $this->getName($propertyNode)) {
                $emptyStringPropertyNode->default = new Array_();

                return true;
            }
        }

        return false;
    }
}
