<?php declare(strict_types=1);

namespace Rector\Rector\Contrib\Nette\Forms;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use Rector\Node\NodeFactory;
use Rector\NodeAnalyzer\PropertyFetchAnalyzer;
use Rector\Rector\AbstractRector;

/**
 * Before:
 * - $control->checkAllowedValues = false;
 *
 * After:
 * - $control->checkDefaultValue(false);
 */
final class ChoiceDefaultValueRector extends AbstractRector
{
    /**
     * @var PropertyFetchAnalyzer
     */
    private $propertyFetchAnalyzer;

    /**
     * @var NodeFactory
     */
    private $nodeFactory;

    public function __construct(PropertyFetchAnalyzer $propertyFetchAnalyzer, NodeFactory $nodeFactory)
    {
        $this->propertyFetchAnalyzer = $propertyFetchAnalyzer;
        $this->nodeFactory = $nodeFactory;
    }

    public function isCandidate(Node $node): bool
    {
        if (! $node instanceof Assign) {
            return false;
        }

        return $this->propertyFetchAnalyzer->isPropertyFetchOnTypes(
            $node->var,
            ['Nette\Forms\Controls\MultiChoiceControl', 'Nette\Forms\Controls\ChoiceControl'],
            'checkAllowedValues'
        );
    }

    /**
     * @param Assign $assignNode
     */
    public function refactor(Node $assignNode): ?Node
    {
        $propertyNode = $assignNode->var->var;

        return $this->nodeFactory->createMethodCallWithVariableAndArguments(
            $propertyNode,
            'checkDefaultValue',
            [$assignNode->expr]
        );
    }
}
