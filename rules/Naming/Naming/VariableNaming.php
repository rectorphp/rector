<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\Naming\Naming;

use RectorPrefix20220606\Nette\Utils\Strings;
use RectorPrefix20220606\PhpParser\Node;
use RectorPrefix20220606\PhpParser\Node\Arg;
use RectorPrefix20220606\PhpParser\Node\Expr;
use RectorPrefix20220606\PhpParser\Node\Expr\Cast;
use RectorPrefix20220606\PhpParser\Node\Expr\FuncCall;
use RectorPrefix20220606\PhpParser\Node\Expr\MethodCall;
use RectorPrefix20220606\PhpParser\Node\Expr\NullsafeMethodCall;
use RectorPrefix20220606\PhpParser\Node\Expr\StaticCall;
use RectorPrefix20220606\PhpParser\Node\Expr\Ternary;
use RectorPrefix20220606\PhpParser\Node\Name;
use RectorPrefix20220606\PhpParser\Node\Scalar\String_;
use RectorPrefix20220606\PHPStan\Analyser\Scope;
use RectorPrefix20220606\PHPStan\Type\ThisType;
use RectorPrefix20220606\PHPStan\Type\Type;
use RectorPrefix20220606\Rector\Core\Exception\NotImplementedYetException;
use RectorPrefix20220606\Rector\Naming\Contract\AssignVariableNameResolverInterface;
use RectorPrefix20220606\Rector\NodeNameResolver\NodeNameResolver;
use RectorPrefix20220606\Rector\NodeTypeResolver\NodeTypeResolver;
use RectorPrefix20220606\Symfony\Component\String\UnicodeString;
final class VariableNaming
{
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\NodeTypeResolver
     */
    private $nodeTypeResolver;
    /**
     * @var AssignVariableNameResolverInterface[]
     * @readonly
     */
    private $assignVariableNameResolvers;
    /**
     * @param AssignVariableNameResolverInterface[] $assignVariableNameResolvers
     */
    public function __construct(NodeNameResolver $nodeNameResolver, NodeTypeResolver $nodeTypeResolver, array $assignVariableNameResolvers)
    {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->assignVariableNameResolvers = $assignVariableNameResolvers;
    }
    public function resolveFromNodeWithScopeCountAndFallbackName(Expr $expr, ?Scope $scope, string $fallbackName) : string
    {
        $name = $this->resolveFromNode($expr);
        if ($name === null) {
            $name = $fallbackName;
        }
        if (\strpos($name, '\\') !== \false) {
            $name = (string) Strings::after($name, '\\', -1);
        }
        $countedValueName = $this->createCountedValueName($name, $scope);
        return \lcfirst($countedValueName);
    }
    public function createCountedValueName(string $valueName, ?Scope $scope) : string
    {
        if ($scope === null) {
            return $valueName;
        }
        // make sure variable name is unique
        if (!$scope->hasVariableType($valueName)->yes()) {
            return $valueName;
        }
        // we need to add number suffix until the variable is unique
        $i = 2;
        $countedValueNamePart = $valueName;
        while ($scope->hasVariableType($valueName)->yes()) {
            $valueName = $countedValueNamePart . $i;
            ++$i;
        }
        return $valueName;
    }
    public function resolveFromFuncCallFirstArgumentWithSuffix(FuncCall $funcCall, string $suffix, string $fallbackName, ?Scope $scope) : string
    {
        $bareName = $this->resolveBareFuncCallArgumentName($funcCall, $fallbackName, $suffix);
        return $this->createCountedValueName($bareName, $scope);
    }
    public function resolveFromNodeAndType(Node $node, Type $type) : ?string
    {
        $variableName = $this->resolveBareFromNode($node);
        if ($variableName === null) {
            return null;
        }
        // adjust static to specific class
        if ($variableName === 'this' && $type instanceof ThisType) {
            $shortClassName = $this->nodeNameResolver->getShortName($type->getClassName());
            $variableName = \lcfirst($shortClassName);
        } else {
            $variableName = $this->nodeNameResolver->getShortName($variableName);
        }
        $variableNameUnicodeString = new UnicodeString($variableName);
        return $variableNameUnicodeString->camel()->toString();
    }
    private function resolveFromNode(Node $node) : ?string
    {
        $nodeType = $this->nodeTypeResolver->getType($node);
        return $this->resolveFromNodeAndType($node, $nodeType);
    }
    private function resolveBareFromNode(Node $node) : ?string
    {
        $node = $this->unwrapNode($node);
        foreach ($this->assignVariableNameResolvers as $assignVariableNameResolver) {
            if ($assignVariableNameResolver->match($node)) {
                return $assignVariableNameResolver->resolve($node);
            }
        }
        if ($node !== null && ($node instanceof MethodCall || $node instanceof NullsafeMethodCall || $node instanceof StaticCall)) {
            return $this->resolveFromMethodCall($node);
        }
        if ($node instanceof FuncCall) {
            return $this->resolveFromNode($node->name);
        }
        if (!$node instanceof Node) {
            throw new NotImplementedYetException();
        }
        $paramName = $this->nodeNameResolver->getName($node);
        if ($paramName !== null) {
            return $paramName;
        }
        if ($node instanceof String_) {
            return $node->value;
        }
        return null;
    }
    /**
     * @param \PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\NullsafeMethodCall|\PhpParser\Node\Expr\StaticCall $node
     */
    private function resolveFromMethodCall($node) : ?string
    {
        if ($node->name instanceof MethodCall) {
            return $this->resolveFromMethodCall($node->name);
        }
        $methodName = $this->nodeNameResolver->getName($node->name);
        if (!\is_string($methodName)) {
            return null;
        }
        return $methodName;
    }
    private function unwrapNode(Node $node) : ?Node
    {
        if ($node instanceof Arg) {
            return $node->value;
        }
        if ($node instanceof Cast) {
            return $node->expr;
        }
        if ($node instanceof Ternary) {
            return $node->if;
        }
        return $node;
    }
    private function resolveBareFuncCallArgumentName(FuncCall $funcCall, string $fallbackName, string $suffix) : string
    {
        if (!isset($funcCall->args[0])) {
            return '';
        }
        if (!$funcCall->args[0] instanceof Arg) {
            return '';
        }
        $argumentValue = $funcCall->args[0]->value;
        if ($argumentValue instanceof MethodCall || $argumentValue instanceof StaticCall) {
            $name = $this->nodeNameResolver->getName($argumentValue->name);
        } else {
            $name = $this->nodeNameResolver->getName($argumentValue);
        }
        if ($name === null) {
            return $fallbackName;
        }
        return $name . $suffix;
    }
}
