<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\ChildPopulator;

use PhpParser\Node\FunctionLike;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PHPStan\Type\Type;
use Rector\ChangesReporting\Collector\RectorChangeCollector;
use Rector\NodeCollector\NodeCollector\NodeRepository;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\TypeDeclaration\NodeTypeAnalyzer\ChildTypeResolver;
use Rector\TypeDeclaration\ValueObject\NewType;
final class ChildParamPopulator
{
    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @var RectorChangeCollector
     */
    private $rectorChangeCollector;
    /**
     * @var NodeRepository
     */
    private $nodeRepository;
    /**
     * @var ChildTypeResolver
     */
    private $childTypeResolver;
    public function __construct(\Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver, \Rector\ChangesReporting\Collector\RectorChangeCollector $rectorChangeCollector, \Rector\NodeCollector\NodeCollector\NodeRepository $nodeRepository, \Rector\TypeDeclaration\NodeTypeAnalyzer\ChildTypeResolver $childTypeResolver)
    {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->rectorChangeCollector = $rectorChangeCollector;
        $this->nodeRepository = $nodeRepository;
        $this->childTypeResolver = $childTypeResolver;
    }
    /**
     * Add typehint to all children
     * @param ClassMethod|Function_ $functionLike
     */
    public function populateChildClassMethod(\PhpParser\Node\FunctionLike $functionLike, int $position, \PHPStan\Type\Type $paramType) : void
    {
        if (!$functionLike instanceof \PhpParser\Node\Stmt\ClassMethod) {
            return;
        }
        /** @var string|null $className */
        $className = $functionLike->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::CLASS_NAME);
        // anonymous class
        if ($className === null) {
            return;
        }
        $childrenClassLikes = $this->nodeRepository->findClassesAndInterfacesByType($className);
        // update their methods as well
        foreach ($childrenClassLikes as $childClassLike) {
            if ($childClassLike instanceof \PhpParser\Node\Stmt\Class_) {
                $usedTraits = $this->nodeRepository->findUsedTraitsInClass($childClassLike);
                foreach ($usedTraits as $usedTrait) {
                    $this->addParamTypeToMethod($usedTrait, $position, $functionLike, $paramType);
                }
            }
            $this->addParamTypeToMethod($childClassLike, $position, $functionLike, $paramType);
        }
    }
    private function addParamTypeToMethod(\PhpParser\Node\Stmt\ClassLike $classLike, int $position, \PhpParser\Node\Stmt\ClassMethod $classMethod, \PHPStan\Type\Type $paramType) : void
    {
        $methodName = $this->nodeNameResolver->getName($classMethod);
        $currentClassMethod = $classLike->getMethod($methodName);
        if (!$currentClassMethod instanceof \PhpParser\Node\Stmt\ClassMethod) {
            return;
        }
        if (!isset($currentClassMethod->params[$position])) {
            return;
        }
        $paramNode = $currentClassMethod->params[$position];
        // already has a type
        if ($paramNode->type !== null) {
            return;
        }
        $resolvedChildType = $this->childTypeResolver->resolveChildTypeNode($paramType);
        if ($resolvedChildType === null) {
            return;
        }
        // let the method know it was changed now
        $paramNode->type = $resolvedChildType;
        $paramNode->type->setAttribute(\Rector\TypeDeclaration\ValueObject\NewType::HAS_NEW_INHERITED_TYPE, \true);
        $this->rectorChangeCollector->notifyNodeFileInfo($paramNode);
    }
}
