<?php

declare (strict_types=1);
namespace Rector\DeadCode\NodeManipulator;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\PhpDocParser\Ast\PhpDoc\ParamTagValueNode;
use PHPStan\Type\ObjectType;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTagRemover;
use Rector\Comments\NodeDocBlock\DocBlockUpdater;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\NodeTypeResolver;
use Rector\PhpParser\Node\BetterNodeFinder;
final class PrivateMethodParamRemover
{
    /**
     * @readonly
     */
    private PhpDocTagRemover $phpDocTagRemover;
    /**
     * @readonly
     */
    private DocBlockUpdater $docBlockUpdater;
    /**
     * @readonly
     */
    private PhpDocInfoFactory $phpDocInfoFactory;
    /**
     * @readonly
     */
    private BetterNodeFinder $betterNodeFinder;
    /**
     * @readonly
     */
    private NodeNameResolver $nodeNameResolver;
    /**
     * @readonly
     */
    private NodeTypeResolver $nodeTypeResolver;
    public function __construct(PhpDocTagRemover $phpDocTagRemover, DocBlockUpdater $docBlockUpdater, PhpDocInfoFactory $phpDocInfoFactory, BetterNodeFinder $betterNodeFinder, NodeNameResolver $nodeNameResolver, NodeTypeResolver $nodeTypeResolver)
    {
        $this->phpDocTagRemover = $phpDocTagRemover;
        $this->docBlockUpdater = $docBlockUpdater;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->betterNodeFinder = $betterNodeFinder;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->nodeTypeResolver = $nodeTypeResolver;
    }
    /**
     * @param array<int, Param> $paramsToRemove
     */
    public function removeParams(Class_ $class, ClassMethod $classMethod, array $paramsToRemove): bool
    {
        // early remove callers, to keep args in sync
        if (!$this->removeCallerArgs($class, $classMethod, $paramsToRemove)) {
            return \false;
        }
        foreach (array_keys($classMethod->params) as $key) {
            if (!isset($paramsToRemove[$key])) {
                continue;
            }
            unset($classMethod->params[$key]);
        }
        // reset param keys
        $classMethod->params = array_values($classMethod->params);
        $this->clearPhpDocInfo($classMethod, $paramsToRemove);
        return \true;
    }
    /**
     * @param array<int, Param> $paramsToRemove
     */
    private function removeCallerArgs(Class_ $class, ClassMethod $classMethod, array $paramsToRemove): bool
    {
        $classMethods = $class->getMethods();
        if ($classMethods === []) {
            return \false;
        }
        $methodName = $this->nodeNameResolver->getName($classMethod);
        if ($methodName === null) {
            return \false;
        }
        $keysArg = array_keys($paramsToRemove);
        $classObjectType = new ObjectType((string) $this->nodeNameResolver->getName($class));
        $callers = [];
        foreach ($classMethods as $currentClassMethod) {
            /** @var MethodCall[]|StaticCall[] $callers */
            $callers = array_merge($callers, $this->resolveCallers($currentClassMethod, $methodName, $classObjectType));
        }
        foreach ($callers as $caller) {
            if ($caller->isFirstClassCallable()) {
                return \false;
            }
            foreach ($caller->getArgs() as $key => $arg) {
                if ($arg->unpack) {
                    return \false;
                }
                if ($arg->name instanceof Identifier) {
                    if (isset($paramsToRemove[$key]) && $this->nodeNameResolver->isName($paramsToRemove[$key], (string) $this->nodeNameResolver->getName($arg->name))) {
                        continue;
                    }
                    return \false;
                }
            }
        }
        foreach ($callers as $caller) {
            $this->cleanupArgs($caller, $keysArg);
        }
        return \true;
    }
    /**
     * @param int[] $keysArg
     * @param \PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\StaticCall $call
     */
    private function cleanupArgs($call, array $keysArg): void
    {
        $args = $call->getArgs();
        foreach (array_keys($args) as $key) {
            if (in_array($key, $keysArg, \true)) {
                unset($args[$key]);
            }
        }
        // reset arg keys
        $call->args = array_values($args);
    }
    /**
     * @return MethodCall[]|StaticCall[]
     */
    private function resolveCallers(ClassMethod $classMethod, string $methodName, ObjectType $classObjectType): array
    {
        return $this->betterNodeFinder->find($classMethod, function (Node $subNode) use ($methodName, $classObjectType): bool {
            if (!$subNode instanceof MethodCall && !$subNode instanceof StaticCall) {
                return \false;
            }
            $nodeToCheck = $subNode instanceof MethodCall ? $subNode->var : $subNode->class;
            if (!$this->nodeTypeResolver->isObjectType($nodeToCheck, $classObjectType)) {
                return \false;
            }
            return $this->nodeNameResolver->isName($subNode->name, $methodName);
        });
    }
    /**
     * @param array<int, Param> $paramsToRemove
     */
    private function clearPhpDocInfo(ClassMethod $classMethod, array $paramsToRemove): void
    {
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($classMethod);
        $hasChanged = \false;
        foreach ($paramsToRemove as $paramToRemove) {
            $parameterName = $this->nodeNameResolver->getName($paramToRemove->var);
            if ($parameterName === null) {
                continue;
            }
            $paramTagValueNode = $phpDocInfo->getParamTagValueByName($parameterName);
            if (!$paramTagValueNode instanceof ParamTagValueNode) {
                continue;
            }
            if ($paramTagValueNode->parameterName !== '$' . $parameterName) {
                continue;
            }
            $hasTagRemoved = $this->phpDocTagRemover->removeTagValueFromNode($phpDocInfo, $paramTagValueNode);
            if ($hasTagRemoved) {
                $hasChanged = \true;
            }
        }
        if ($hasChanged) {
            $this->docBlockUpdater->updateRefactoredNodeWithPhpDocInfo($classMethod);
        }
    }
}
