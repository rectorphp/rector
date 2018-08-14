<?php declare(strict_types=1);

namespace Rector\PhpParser\Rector;

use PhpParser\Node;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use Rector\Builder\IdentifierRenamer;
use Rector\Node\PropertyFetchNodeFactory;
use Rector\NodeAnalyzer\PropertyFetchAnalyzer;
use Rector\NodeTypeResolver\Node\Attribute;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

final class CatchAndClosureUseNameRector extends AbstractRector
{
    /**
     * @var IdentifierRenamer
     */
    private $identifierRenamer;

    /**
     * @var PropertyFetchAnalyzer
     */
    private $propertyFetchAnalyzer;

    /**
     * @var PropertyFetchNodeFactory
     */
    private $propertyFetchNodeFactory;

    public function __construct(
        IdentifierRenamer $identifierRenamer,
        PropertyFetchAnalyzer $propertyFetchAnalyzer,
        PropertyFetchNodeFactory $propertyFetchNodeFactory
    ) {
        $this->identifierRenamer = $identifierRenamer;
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

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Turns `$catchNode->var` to its new `name` property in php-parser', [
            new CodeSample('$catchNode->var;', '$catchNode->var->name'),
        ]);
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
        $this->identifierRenamer->renameNode($propertyFetchNode, 'name');

        return $propertyFetchNode;
    }
}
