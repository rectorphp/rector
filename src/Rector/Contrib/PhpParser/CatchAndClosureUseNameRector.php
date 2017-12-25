<?php declare(strict_types=1);

namespace Rector\Rector\Contrib\PhpParser;

use PhpParser\Node;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use Rector\Node\Attribute;
use Rector\Node\PropertyFetchNodeFactory;
use Rector\NodeAnalyzer\PropertyFetchAnalyzer;
use Rector\NodeChanger\MethodNameChanger;
use Rector\Rector\AbstractRector;

/**
 * Before:
 * - $catchNode->var
 *
 * After:
 * - $catchNode->var->name
 */
final class CatchAndClosureUseNameRector extends AbstractRector
{
    /**
     * @var MethodNameChanger
     */
    private $methodNameChanger;

    /**
     * @var PropertyFetchAnalyzer
     */
    private $propertyFetchAnalyzer;

    /**
     * @var PropertyFetchNodeFactory
     */
    private $propertyFetchNodeFactory;

    public function __construct(
        MethodNameChanger $methodNameChanger,
        PropertyFetchAnalyzer $propertyFetchAnalyzer,
        PropertyFetchNodeFactory $propertyFetchNodeFactory
    ) {
        $this->methodNameChanger = $methodNameChanger;
        $this->propertyFetchAnalyzer = $propertyFetchAnalyzer;
        $this->propertyFetchNodeFactory = $propertyFetchNodeFactory;
    }

    public function isCandidate(Node $node): bool
    {
        return $this->propertyFetchAnalyzer->isTypesAndProperty(
            $node,
            ['PhpParser\Node\Stmt\Catch_', 'PhpParser\Node\Expr\ClosureUse'],
            'var'
        );
    }

    /**
     * @param PropertyFetch $propertyFetchNode
     */
    public function refactor(Node $propertyFetchNode): ?Node
    {
        $parentNode = $propertyFetchNode->getAttribute(Attribute::PARENT_NODE);
        if ($parentNode instanceof PropertyFetch) {
            return $propertyFetchNode;
        }

        /** @var Variable $variableNode */
        $variableNode = $propertyFetchNode->var;

        $propertyFetchNode->var = $this->propertyFetchNodeFactory->createWithVariableNameAndPropertyName(
            (string) $variableNode->name,
            'var'
        );
        $this->methodNameChanger->renameNode($propertyFetchNode, 'name');

        return $propertyFetchNode;
    }
}
