<?php

declare (strict_types=1);
namespace Rector\Nette\NodeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Scalar\String_;
use PHPStan\Type\ObjectType;
use Rector\NodeTypeResolver\NodeTypeResolver;
final class ControlDimFetchAnalyzer
{
    /**
     * @var \Rector\NodeTypeResolver\NodeTypeResolver
     */
    private $nodeTypeResolver;
    public function __construct(\Rector\NodeTypeResolver\NodeTypeResolver $nodeTypeResolver)
    {
        $this->nodeTypeResolver = $nodeTypeResolver;
    }
    public function matchNameOnFormOrControlVariable(\PhpParser\Node $node) : ?string
    {
        return $this->matchNameOnVariableType($node, new \PHPStan\Type\ObjectType('Nette\\Application\\UI\\Form'));
    }
    public function matchNameOnControlVariable(\PhpParser\Node $node) : ?string
    {
        return $this->matchNameOnVariableType($node, new \PHPStan\Type\ObjectType('Nette\\Application\\UI\\Control'));
    }
    public function matchName(\PhpParser\Node $node) : ?string
    {
        if (!$node instanceof \PhpParser\Node\Expr\ArrayDimFetch) {
            return null;
        }
        if (!$this->isVariableTypes($node->var, [new \PHPStan\Type\ObjectType('Nette\\ComponentModel\\IContainer')])) {
            return null;
        }
        if (!$node->dim instanceof \PhpParser\Node\Scalar\String_) {
            return null;
        }
        return $node->dim->value;
    }
    private function matchNameOnVariableType(\PhpParser\Node $node, \PHPStan\Type\ObjectType $objectType) : ?string
    {
        $matchedName = $this->matchName($node);
        if ($matchedName === null) {
            return null;
        }
        /** @var Assign $node */
        if (!$this->isVariableTypes($node->var, [$objectType])) {
            return null;
        }
        return $matchedName;
    }
    /**
     * @param ObjectType[] $objectTypes
     */
    private function isVariableTypes(\PhpParser\Node $node, array $objectTypes) : bool
    {
        if (!$node instanceof \PhpParser\Node\Expr\Variable) {
            return \false;
        }
        foreach ($objectTypes as $objectType) {
            if ($this->nodeTypeResolver->isObjectType($node, $objectType)) {
                return \true;
            }
        }
        return \false;
    }
}
