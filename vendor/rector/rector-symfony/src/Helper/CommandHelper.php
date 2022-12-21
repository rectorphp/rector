<?php

declare (strict_types=1);
namespace Rector\Symfony\Helper;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\NodeTraverser;
use PHPStan\Type\ObjectType;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeRemoval\NodeRemover;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\NodeTypeResolver;
use Rector\Php80\NodeAnalyzer\PhpAttributeAnalyzer;
use Rector\PhpDocParser\NodeTraverser\SimpleCallableNodeTraverser;
/**
 * @see \Rector\Symfony\Tests\Rector\Class_\CommandPropertyToAttributeRector\CommandPropertyToAttributeRectorTest
 */
final class CommandHelper
{
    public const ATTRIBUTE = 'Symfony\\Component\\Console\\Attribute\\AsCommand';
    /**
     * @readonly
     * @var \Rector\Php80\NodeAnalyzer\PhpAttributeAnalyzer
     */
    private $phpAttributeAnalyzer;
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
     * @readonly
     * @var \Rector\PhpDocParser\NodeTraverser\SimpleCallableNodeTraverser
     */
    private $simpleCallableNodeTraverser;
    /**
     * @readonly
     * @var \Rector\NodeRemoval\NodeRemover
     */
    private $nodeRemover;
    public function __construct(PhpAttributeAnalyzer $phpAttributeAnalyzer, NodeNameResolver $nodeNameResolver, NodeTypeResolver $nodeTypeResolver, SimpleCallableNodeTraverser $simpleCallableNodeTraverser, NodeRemover $nodeRemover)
    {
        $this->phpAttributeAnalyzer = $phpAttributeAnalyzer;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->simpleCallableNodeTraverser = $simpleCallableNodeTraverser;
        $this->nodeRemover = $nodeRemover;
    }
    public function getCommandAliasesValueFromAttributeOrSetter(Class_ $class) : ?Array_
    {
        $commandAliases = null;
        $classMethod = $class->getMethod('configure');
        if (!$classMethod instanceof ClassMethod) {
            return $this->resolveAliasesFromAttribute($class);
        }
        $this->simpleCallableNodeTraverser->traverseNodesWithCallable((array) $classMethod->stmts, function (Node $node) use(&$commandAliases) {
            if (!$node instanceof MethodCall) {
                return null;
            }
            if ($node->isFirstClassCallable()) {
                return null;
            }
            if (!$this->nodeTypeResolver->isObjectType($node->var, new ObjectType('Symfony\\Component\\Console\\Command\\Command'))) {
                return null;
            }
            if (!$this->nodeNameResolver->isName($node->name, 'setAliases')) {
                return null;
            }
            /** @var Arg $arg */
            $arg = $node->args[0];
            if (!$arg->value instanceof Array_) {
                return null;
            }
            $commandAliases = $arg->value;
            $parentNode = $node->getAttribute(AttributeKey::PARENT_NODE);
            if ($parentNode instanceof MethodCall) {
                $parentNode->var = $node->var;
            } else {
                $this->nodeRemover->removeNode($node);
            }
            return NodeTraverser::STOP_TRAVERSAL;
        });
        return $commandAliases;
    }
    public function getCommandHiddenValueFromAttributeOrSetter(Class_ $class) : ?ConstFetch
    {
        $commandHidden = null;
        $classMethod = $class->getMethod('configure');
        if (!$classMethod instanceof ClassMethod) {
            return $this->resolveHiddenFromAttribute($class);
        }
        $this->simpleCallableNodeTraverser->traverseNodesWithCallable((array) $classMethod->stmts, function (Node $node) use(&$commandHidden) {
            if (!$node instanceof MethodCall) {
                return null;
            }
            if ($node->isFirstClassCallable()) {
                return null;
            }
            if (!$this->nodeTypeResolver->isObjectType($node->var, new ObjectType('Symfony\\Component\\Console\\Command\\Command'))) {
                return null;
            }
            if (!$this->nodeNameResolver->isName($node->name, 'setHidden')) {
                return null;
            }
            $commandHidden = $this->getCommandHiddenValue($node);
            $parentNode = $node->getAttribute(AttributeKey::PARENT_NODE);
            if ($parentNode instanceof MethodCall) {
                $parentNode->var = $node->var;
            } else {
                $this->nodeRemover->removeNode($node);
            }
            return NodeTraverser::STOP_TRAVERSAL;
        });
        return $commandHidden;
    }
    public function getCommandHiddenValue(MethodCall $methodCall) : ?ConstFetch
    {
        if (!isset($methodCall->args[0])) {
            return new ConstFetch(new Name('true'));
        }
        /** @var Arg $arg */
        $arg = $methodCall->args[0];
        if (!$arg->value instanceof ConstFetch) {
            return null;
        }
        return $arg->value;
    }
    /**
     * @return string|\PhpParser\Node\Expr\ConstFetch|\PhpParser\Node\Expr\Array_|null
     */
    public function getArgumentValueFromAttribute(Class_ $class, int $argumentIndexKey)
    {
        $argumentValue = null;
        foreach ($class->attrGroups as $attrGroup) {
            foreach ($attrGroup->attrs as $attribute) {
                if (!$this->nodeNameResolver->isName($attribute->name, self::ATTRIBUTE)) {
                    continue;
                }
                if (!isset($attribute->args[$argumentIndexKey])) {
                    continue;
                }
                $arg = $attribute->args[$argumentIndexKey];
                if ($arg->value instanceof String_) {
                    $argumentValue = $arg->value->value;
                } elseif ($arg->value instanceof ConstFetch || $arg->value instanceof Array_) {
                    $argumentValue = $arg->value;
                }
            }
        }
        return $argumentValue;
    }
    private function resolveHiddenFromAttribute(Class_ $class) : ?ConstFetch
    {
        $commandHidden = null;
        if ($this->phpAttributeAnalyzer->hasPhpAttribute($class, self::ATTRIBUTE)) {
            $commandHiddenFromArgument = $this->getArgumentValueFromAttribute($class, 3);
            if ($commandHiddenFromArgument instanceof ConstFetch) {
                $commandHidden = $commandHiddenFromArgument;
            }
        }
        return $commandHidden;
    }
    private function resolveAliasesFromAttribute(Class_ $class) : ?Array_
    {
        $commandAliases = null;
        if ($this->phpAttributeAnalyzer->hasPhpAttribute($class, self::ATTRIBUTE)) {
            $commandAliasesFromArgument = $this->getArgumentValueFromAttribute($class, 2);
            if ($commandAliasesFromArgument instanceof Array_) {
                $commandAliases = $commandAliasesFromArgument;
            }
        }
        return $commandAliases;
    }
}
