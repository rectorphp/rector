<?php

declare (strict_types=1);
namespace Rector\Naming\Naming;

use RectorPrefix202208\Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Cast;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\NullsafeMethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Ternary;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\String_;
use PHPStan\Analyser\MutatingScope;
use PHPStan\Analyser\Scope;
use PHPStan\Type\ThisType;
use PHPStan\Type\Type;
use Rector\Core\Exception\NotImplementedYetException;
use Rector\Naming\Contract\AssignVariableNameResolverInterface;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\NodeTypeResolver;
use RectorPrefix202208\Symfony\Component\String\UnicodeString;
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
    public function resolveFromNodeWithScopeCountAndFallbackName(Expr $expr, MutatingScope $mutatingScope, string $fallbackName) : string
    {
        $name = $this->resolveFromNode($expr);
        if ($name === null) {
            $name = $fallbackName;
        }
        if (\strpos($name, '\\') !== \false) {
            $name = (string) Strings::after($name, '\\', -1);
        }
        $countedValueName = $this->createCountedValueName($name, $mutatingScope);
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
    /**
     * @api
     */
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
