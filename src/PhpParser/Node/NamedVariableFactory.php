<?php

declare (strict_types=1);
namespace Rector\Core\PhpParser\Node;

use PhpParser\Node;
use PhpParser\Node\Expr\Variable;
use Rector\Core\Exception\ShouldNotHappenException;
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
    public function __construct(\Rector\Naming\Naming\VariableNaming $variableNaming, \Rector\Core\PhpParser\Node\BetterNodeFinder $betterNodeFinder)
    {
        $this->variableNaming = $variableNaming;
        $this->betterNodeFinder = $betterNodeFinder;
    }
    public function createVariable(\PhpParser\Node $node, string $variableName) : \PhpParser\Node\Expr\Variable
    {
        $currentStmt = $this->betterNodeFinder->resolveCurrentStatement($node);
        if (!$currentStmt instanceof \PhpParser\Node) {
            throw new \Rector\Core\Exception\ShouldNotHappenException();
        }
        $scope = $currentStmt->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::SCOPE);
        $variableName = $this->variableNaming->createCountedValueName($variableName, $scope);
        return new \PhpParser\Node\Expr\Variable($variableName);
    }
}
