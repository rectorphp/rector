<?php declare(strict_types=1);

namespace Rector\Rector\Contrib\Nette\Forms;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use Rector\Node\MethodCallNodeFactory;
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
     * @var MethodCallNodeFactory
     */
    private $methodCallNodeFactory;

    public function __construct(
        PropertyFetchAnalyzer $propertyFetchAnalyzer,
        MethodCallNodeFactory $methodCallNodeFactory
    ) {
        $this->propertyFetchAnalyzer = $propertyFetchAnalyzer;
        $this->methodCallNodeFactory = $methodCallNodeFactory;
    }

    public function isCandidate(Node $node): bool
    {
        if (! $node instanceof Assign) {
            return false;
        }

        return $this->propertyFetchAnalyzer->isTypesAndProperty(
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

        return $this->methodCallNodeFactory->createMethodCallWithVariableAndArguments(
            $propertyNode,
            'checkDefaultValue',
            [$assignNode->expr]
        );
    }
}
