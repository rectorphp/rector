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
use PhpParser\Node\Stmt\Expression;
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
    public function resolveCommandAliasesFromAttributeOrSetter(Class_ $class) : ?Array_
    {
        $classMethod = $class->getMethod('configure');
        if (!$classMethod instanceof ClassMethod) {
            return $this->resolveCommandAliasesFromAttribute($class);
        }
        if ($classMethod->stmts === null) {
            return null;
        }
        $aliasesArray = $this->resolveFromStmtSetterMethodCall($classMethod);
        if ($aliasesArray instanceof Array_) {
            return $aliasesArray;
        }
        $aliasesArray = null;
        $this->simpleCallableNodeTraverser->traverseNodesWithCallable($classMethod, function (Node $node) use(&$aliasesArray) {
            if (!$node instanceof MethodCall) {
                return null;
            }
            if (!$this->isSetAliasesMethodCall($node)) {
                return null;
            }
            $firstArgValue = $node->getArgs()[0]->value;
            if (!$firstArgValue instanceof Array_) {
                return null;
            }
            $aliasesArray = $firstArgValue;
            return $node->var;
        });
        return $aliasesArray;
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
            if (!$this->nodeNameResolver->isName($node->name, 'setHidden')) {
                return null;
            }
            if (!$this->nodeTypeResolver->isObjectType($node->var, new ObjectType('Symfony\\Component\\Console\\Command\\Command'))) {
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
    private function isSetAliasesMethodCall(MethodCall $methodCall) : bool
    {
        if (!$this->nodeNameResolver->isName($methodCall->name, 'setAliases')) {
            return \false;
        }
        return $this->nodeTypeResolver->isObjectType($methodCall->var, new ObjectType('Symfony\\Component\\Console\\Command\\Command'));
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
    private function resolveCommandAliasesFromAttribute(Class_ $class) : ?Array_
    {
        if (!$this->phpAttributeAnalyzer->hasPhpAttribute($class, self::ATTRIBUTE)) {
            return null;
        }
        $commandAliasesFromArgument = $this->getArgumentValueFromAttribute($class, 2);
        if ($commandAliasesFromArgument instanceof Array_) {
            return $commandAliasesFromArgument;
        }
        return null;
    }
    private function resolveFromStmtSetterMethodCall(ClassMethod $classMethod) : ?\PhpParser\Node\Expr\Array_
    {
        if ($classMethod->stmts === null) {
            return null;
        }
        foreach ($classMethod->stmts as $key => $stmt) {
            if (!$stmt instanceof Expression) {
                continue;
            }
            if (!$stmt->expr instanceof MethodCall) {
                continue;
            }
            $methodCall = $stmt->expr;
            if (!$this->isSetAliasesMethodCall($methodCall)) {
                continue;
            }
            $arg = $methodCall->getArgs()[0];
            if (!$arg->value instanceof Array_) {
                return null;
            }
            unset($classMethod->stmts[$key]);
            return $arg->value;
        }
        return null;
    }
}
