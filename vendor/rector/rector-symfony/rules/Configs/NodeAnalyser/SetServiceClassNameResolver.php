<?php

declare (strict_types=1);
namespace Rector\Symfony\Configs\NodeAnalyser;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\NodeFinder;
final class SetServiceClassNameResolver
{
    /**
     * Looks for
     *
     * $services->set(SomeClassName::Class)
     *
     * ↓
     * "SomeClassName"
     */
    public function resolve(MethodCall $methodCall) : ?string
    {
        $nodeFinder = new NodeFinder();
        $serviceClassName = null;
        $nodeFinder->findFirst($methodCall, function (Node $node) use(&$serviceClassName) : ?bool {
            if (!$node instanceof MethodCall) {
                return null;
            }
            if (!$node->name instanceof Identifier) {
                return null;
            }
            // we look for services variable
            if (!$this->isServicesVariable($node->var)) {
                return null;
            }
            // dump($methodCall->var);
            $args = $node->getArgs();
            foreach ($args as $arg) {
                if (!$arg->value instanceof ClassConstFetch) {
                    continue;
                }
                $resolvedClassConstantName = $this->matchClassConstantName($arg->value);
                if (!\is_string($resolvedClassConstantName)) {
                    continue;
                }
                $serviceClassName = $resolvedClassConstantName;
                return \true;
            }
            return \false;
        });
        return $serviceClassName;
    }
    public function matchClassConstantName(ClassConstFetch $classConstFetch) : ?string
    {
        if (!$classConstFetch->name instanceof Identifier) {
            return null;
        }
        if ($classConstFetch->name->toString() !== 'class') {
            return null;
        }
        if (!$classConstFetch->class instanceof FullyQualified) {
            return null;
        }
        return $classConstFetch->class->toString();
    }
    /**
     * Dummy name check, as we don't have types here, only variable names.
     */
    private function isServicesVariable(Expr $expr) : bool
    {
        if (!$expr instanceof Variable) {
            return \false;
        }
        if (!\is_string($expr->name)) {
            return \false;
        }
        $servicesName = $expr->name;
        return $servicesName === 'services';
    }
}
