<?php declare(strict_types=1);

namespace Rector\Rector\Contrib\PhpParser;

use PhpParser\Node;
use PhpParser\Node\Expr\PropertyFetch;
use Rector\NodeAnalyzer\PropertyFetchAnalyzer;
use Rector\NodeChanger\MethodNameChanger;
use Rector\Rector\AbstractRector;

/**
 * Before:
 * - $paramNode->name
 * - $staticVarNode->name
 *
 * After:
 * - $paramNode->var->name
 * - $staticVarNode->var->name
 */
final class ParamAndStaticVarNameRector extends AbstractRector
{
    /**
     * @var MethodNameChanger
     */
    private $methodNameChanger;

    /**
     * @var PropertyFetchAnalyzer
     */
    private $propertyFetchAnalyzer;

    public function __construct(MethodNameChanger $methodNameChanger, PropertyFetchAnalyzer $propertyFetchAnalyzer)
    {
        $this->methodNameChanger = $methodNameChanger;
        $this->propertyFetchAnalyzer = $propertyFetchAnalyzer;
    }

    public function isCandidate(Node $node): bool
    {
        $types = [
            'PhpParser\Node\Param',
            'PhpParser\Node\Stmt\StaticVar',
        ];

        return $this->propertyFetchAnalyzer->isTypesAndProperty($node, $types, 'name');
    }

    /**
     * @param PropertyFetch $propertyFetchNode
     */
    public function refactor(Node $propertyFetchNode): ?Node
    {
        $this->methodNameChanger->renameNode($propertyFetchNode, 'var');

        return new PropertyFetch($propertyFetchNode, 'name');
    }
}
