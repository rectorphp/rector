<?php declare(strict_types=1);

namespace Rector\Rector\Contrib\PhpParser;

use PhpParser\Node;
use PhpParser\Node\Expr\PropertyFetch;
use Rector\Builder\IdentifierRenamer;
use Rector\NodeAnalyzer\PropertyFetchAnalyzer;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

final class ParamAndStaticVarNameRector extends AbstractRector
{
    /**
     * @var IdentifierRenamer
     */
    private $identifierRenamer;

    /**
     * @var PropertyFetchAnalyzer
     */
    private $propertyFetchAnalyzer;

    public function __construct(IdentifierRenamer $identifierRenamer, PropertyFetchAnalyzer $propertyFetchAnalyzer)
    {
        $this->identifierRenamer = $identifierRenamer;
        $this->propertyFetchAnalyzer = $propertyFetchAnalyzer;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Turns old string `var` to `var->name` sub-variable in Node of PHP-Parser', [
            new CodeSample('$paramNode->name;', '$paramNode->var->name;'),
            new CodeSample('$staticVarNode->name;', '$staticVarNode->var->name;'),
        ]);
    }

    public function isCandidate(Node $node): bool
    {
        $types = ['PhpParser\Node\Param', 'PhpParser\Node\Stmt\StaticVar'];

        return $this->propertyFetchAnalyzer->isTypesAndProperty($node, $types, 'name');
    }

    /**
     * @param PropertyFetch $propertyFetchNode
     */
    public function refactor(Node $propertyFetchNode): ?Node
    {
        $this->identifierRenamer->renameNode($propertyFetchNode, 'var');

        return new PropertyFetch($propertyFetchNode, 'name');
    }
}
