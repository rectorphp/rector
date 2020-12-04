<?php

declare(strict_types=1);

namespace Rector\DowngradePhp72\Rector\ClassMethod;

use PhpParser\Node;
use ReflectionClass;
use ReflectionMethod;
use PHPStan\Type\Type;
use ReflectionNamedType;
use ReflectionParameter;
use PhpParser\Node\Param;
use PHPStan\Analyser\Scope;
use PHPStan\Type\MixedType;
use PhpParser\Node\UnionType;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Interface_;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\NullableType;
use PhpParser\Node\Stmt\Function_;
use PHPStan\Type\TypeWithClassName;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\PHPStan\Type\ShortenedObjectType;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\TypeDeclaration\ValueObject\NewType;
use Rector\TypeDeclaration\TypeInferer\ParamTypeInferer;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use Rector\PHPStanStaticTypeMapper\PHPStanStaticTypeMapper;
use Rector\TypeDeclaration\ChildPopulator\ChildParamPopulator;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Rector\TypeDeclaration\Rector\FunctionLike\AbstractTypeDeclarationRector;
use Rector\DowngradePhp71\Rector\FunctionLike\AbstractDowngradeParamDeclarationRector;
use PHPStan\Reflection\ReflectionProvider;
use Rector\Core\Reflection\ClassReflectionToAstResolver;
use PHPStan\Reflection\ClassReflection;
use Rector\NodeCollector\NodeCollector\NodeRepository;
use PhpParser\Node\Stmt\ClassLike;
use Rector\ChangesReporting\Collector\RectorChangeCollector;

/**
 * @see https://www.php.net/manual/en/migration72.new-features.php#migration72.new-features.param-type-widening
 *
 * @see \Rector\DowngradePhp72\Tests\Rector\ClassMethod\DowngradeParameterTypeWideningRector\DowngradeParameterTypeWideningRectorTest
 */
final class DowngradeParameterTypeWideningRector extends AbstractTypeDeclarationRector
{
    /**
     * @var ParamTypeInferer
     */
    private $paramTypeInferer;

    /**
     * @var ChildParamPopulator
     */
    private $childParamPopulator;

    /**
     * @var RectorChangeCollector
     */
    private $rectorChangeCollector;

    public function __construct(ChildParamPopulator $childParamPopulator, ParamTypeInferer $paramTypeInferer, RectorChangeCollector $rectorChangeCollector)
    {
        $this->paramTypeInferer = $paramTypeInferer;
        $this->childParamPopulator = $childParamPopulator;
        $this->rectorChangeCollector = $rectorChangeCollector;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Remove argument type declarations in the parent and in all child classes, whenever some child class removes it', [
            new CodeSample(
                <<<'CODE_SAMPLE'
interface A
{
    public function test(array $input);
}

class B implements A
{
    public function test($input){} // type omitted for $input
}

class C implements A
{
    public function test(array $input){}
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
interface A
{
    /**
     * @param array $input
     */
    public function test($input);
}

class B implements A
{
    public function test($input){} // type omitted for $input
}

class C implements A
{
    /**
     * @param array $input
     */
    public function test($input);
}
CODE_SAMPLE
            ),
        ]);
    }

    /**
     * @param ClassMethod|Function_ $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($node->params === null || $node->params === []) {
            return null;
        }

        foreach ($node->params as $position => $param) {
            $this->refactorParamForAncestorsAndSiblings($param, $node, (int) $position);
        }

        return null;
    }

    private function refactorParamForAncestorsAndSiblings(Param $param, FunctionLike $functionLike, int $position): void
    {
        // The param on the child class must have no type
        if ($param->type !== null) {
            return;
        }

        /** @var Scope|null $scope */
        $scope = $functionLike->getAttribute(AttributeKey::SCOPE);
        if ($scope === null) {
            // possibly trait
            return;
        }

        $classReflection = $scope->getClassReflection();
        if ($classReflection === null) {
            return;
        }

        $paramName = $this->getName($param);

        /** @var string $methodName */
        $methodName = $this->getName($functionLike);
        // Obtain the list of the ancestors with a different signature
        $refactorableAncestorClasses = [];
        foreach ($classReflection->getParentClassesNames() as $parentClassName) {
            if (! method_exists($parentClassName, $methodName)) {
                continue;
            }

            if ($this->hasMethodWithTypedParam($classReflection, $parentClassName, $methodName, $paramName)) {
                $refactorableAncestorClasses[] = $parentClassName;
            }
        }
        $refactorableInterfaceClasses = [];
        foreach ($classReflection->getInterfaces() as $interfaceReflection) {
            $interfaceClassName = $interfaceReflection->getName();
            if (! method_exists($interfaceClassName, $methodName)) {
                continue;
            }

            if ($this->hasMethodWithTypedParam($classReflection, $interfaceClassName, $methodName, $paramName)) {
                $refactorableInterfaceClasses[] = $interfaceClassName;
            }
        }

        // Remove the types in all ancestors, and in their descendant classes
        foreach ($refactorableAncestorClasses as $ancestorClass) {
            // $ancestorClassNode = $this->nodeRepository->findClass($ancestorClass);
            // $ancestorClassNode->type = null;
            $childrenClassLikes = $this->nodeRepository->findClassesAndInterfacesByType($ancestorClass);
            foreach ($childrenClassLikes as $childrenClassLike) {
                // $this->childParamPopulator->populateChildClassMethod($ancestorClassNode, $position, null, true);
                //
            }
        }
        foreach ($refactorableInterfaceClasses as $interfaceClass) {
            // $interfaceNode = $this->nodeRepository->findInterface($interfaceClass);
            // // $interfaceNode->type = null;
            /** @var ClassMethod */
            $methodNode = $this->nodeRepository->findClassMethod($interfaceClass, $methodName);
            foreach ($methodNode->params as $methodParam) {
                if ($this->getName($methodParam) == $paramName) {
                    // Add the type in the PHPDoc
                    /** @var PhpDocInfo|null */
                    $phpDocInfo = $methodNode->getAttribute(AttributeKey::PHP_DOC_INFO);
                    if ($phpDocInfo === null) {
                        $phpDocInfo = $this->phpDocInfoFactory->createEmpty($methodNode);
                    }

                    $paramType = $methodParam->type;
                    if ($paramType !== null) {
                        $type = $this->staticTypeMapper->mapPhpParserNodePHPStanType($paramType);
                        $phpDocInfo->changeParamType($type, $methodParam, $paramName);
                    }
                    $methodParam->type = null;
                    break;
                }
            }



            $childrenClassLikes = $this->nodeRepository->findClassesAndInterfacesByType($interfaceClass);

            // update their methods as well
            foreach ($childrenClassLikes as $childClassLike) {
                // if ($childClassLike instanceof Class_) {
                //     $usedTraits = $this->nodeRepository->findUsedTraitsInClass($childClassLike);

                //     foreach ($usedTraits as $trait) {
                //         $this->removeParamTypeFromMethod($trait, $position, $functionLike, $paramType, $changePhpDoc);
                //     }
                // }

                $this->removeParamTypeFromMethod($childClassLike, $position, $methodNode);
            }

            // $this->childParamPopulator->populateChildClassMethod($methodNode, $position, null, true);

            // $interfaceNode = $this->nodeRepository->findInterface($interfaceClass);
            // $childrenClassLikes = $this->nodeRepository->findClassesAndInterfacesByType($interfaceClass);
            // foreach ($childrenClassLikes as $childrenClassLike) {
            //     //
            // }
        }
    }

    private function removeParamTypeFromMethod(
        ClassLike $classLike,
        int $position,
        ClassMethod $classMethod
    ): void {
        $methodName = $this->getName($classMethod);
        if ($methodName === null) {
            return;
        }

        $currentClassMethod = $classLike->getMethod($methodName);
        if ($currentClassMethod === null) {
            return;
        }

        if (! isset($currentClassMethod->params[$position])) {
            return;
        }

        $paramNode = $currentClassMethod->params[$position];

        // It already has no type => nothing to do
        if ($paramNode->type === null) {
            return;
        }

        // Add the current type in the PHPDoc
        /** @var PhpDocInfo|null */
        $phpDocInfo = $classMethod->getAttribute(AttributeKey::PHP_DOC_INFO);
        if ($phpDocInfo === null) {
            $phpDocInfo = $this->phpDocInfoFactory->createEmpty($classMethod);
        }
        $paramName = $this->getName($paramNode);
        $mappedCurrentParamType = $this->staticTypeMapper->mapPhpParserNodePHPStanType($paramNode->type);
        $phpDocInfo->changeParamType($mappedCurrentParamType, $paramNode, $paramName);

        // Remove the type
        $paramNode->type = null;

        $this->rectorChangeCollector->notifyNodeFileInfo($classMethod);
        $this->rectorChangeCollector->notifyNodeFileInfo($paramNode);
    }

    private function hasMethodWithTypedParam(ClassReflection $classReflection, string $parentClassName, string $methodName, string $paramName): bool
    {
        $parentReflectionMethod = new ReflectionMethod($parentClassName, $methodName);
        /** @var ReflectionParameter[] */
        $parentReflectionMethodParams = $parentReflectionMethod->getParameters();
        foreach ($parentReflectionMethodParams as $reflectionParameter) {
            if ($reflectionParameter->name === $paramName && $reflectionParameter->getType() !== null) {
                return true;
            }
        }

        return false;
    }
}
