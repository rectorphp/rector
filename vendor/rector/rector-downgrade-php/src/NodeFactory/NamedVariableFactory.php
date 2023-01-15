<?php

declare (strict_types=1);
namespace Rector\NodeFactory;

use PhpParser\Node;
use PhpParser\Node\Expr\Variable;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\Naming\Naming\VariableNaming;
use Rector\NodeTypeResolver\Node\AttributeKey;
final class NamedVariableFactory
{
    /**
     * @readonly
     * @var \Rector\Naming\Naming\VariableNaming
     */
    private $variableNaming;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    public function __construct(VariableNaming $variableNaming, BetterNodeFinder $betterNodeFinder)
    {
        $this->variableNaming = $variableNaming;
        $this->betterNodeFinder = $betterNodeFinder;
    }
    public function createVariable(Node $node, string $variableName) : Variable
    {
        $currentStmt = $this->betterNodeFinder->resolveCurrentStatement($node);
        if (!$currentStmt instanceof Node) {
            throw new ShouldNotHappenException();
        }
        $scope = $currentStmt->getAttribute(AttributeKey::SCOPE);
        $variableName = $this->variableNaming->createCountedValueName($variableName, $scope);
        return new Variable($variableName);
    }
}
