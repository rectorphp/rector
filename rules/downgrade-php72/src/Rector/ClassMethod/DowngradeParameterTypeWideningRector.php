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

        /** @var string $methodName */
        $methodName = $this->getName($functionLike);
        $paramName = $this->getName($param);

        // Obtain the list of the ancestors classes and implemented interfaces
        // with a different signature
        $refactorableAncestorAndInterfaceClassNames = [];
        $ancestorAndInterfaceClassNames = array_merge(
            $classReflection->getParentClassesNames(),
            array_map(
                function (ClassReflection $interfaceReflection): string {
                    return $interfaceReflection->getName();
                },
                $classReflection->getInterfaces()
            )
        );
        foreach ($ancestorAndInterfaceClassNames as $parentClassName) {
            if (! method_exists($parentClassName, $methodName)) {
                continue;
            }

            if ($this->hasMethodWithTypedParam($parentClassName, $methodName, $paramName)) {
                $refactorableAncestorAndInterfaceClassNames[] = $parentClassName;
            }
        }

        // Remove the types in:
        // - all ancestors + their descendant classes
        // - all implemented interfaces + their implementing classes
        foreach ($refactorableAncestorAndInterfaceClassNames as $parentClassName) {
            /** @var ClassMethod */
            $classMethod = $this->nodeRepository->findClassMethod($parentClassName, $methodName);
            foreach ($classMethod->params as $methodParam) {
                if ($this->getName($methodParam) == $paramName) {
                    // Add the current type in the PHPDoc
                    if ($methodParam->type !== null) {
                        $this->addPHPDocParamTypeToMethod($classMethod, $methodParam);
                    }
                    // Remove the type
                    $methodParam->type = null;
                    break;
                }
            }

            $childrenClassLikes = $this->nodeRepository->findClassesAndInterfacesByType($parentClassName);
            foreach ($childrenClassLikes as $childClassLike) {
                // If the class is implementing the method, then refactor it
                $childClassName = $childClassLike->getAttribute(AttributeKey::CLASS_NAME);
                if ($childClassName === null) {
                    continue;
                }
                $childClassMethod = $this->nodeRepository->findClassMethod($childClassName, $methodName);
                if ($childClassMethod === null) {
                    continue;
                }
                $this->removeParamTypeFromMethod($childClassLike, $position, $childClassMethod);
            }
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

        $param = $currentClassMethod->params[$position];

        // It already has no type => nothing to do
        if ($param->type === null) {
            return;
        }

        // Add the current type in the PHPDoc
        $this->addPHPDocParamTypeToMethod($classMethod, $param);

        // Remove the type
        $param->type = null;

        $this->rectorChangeCollector->notifyNodeFileInfo($param);
    }

    /**
     * Add the current param type in the PHPDoc
     */
    private function addPHPDocParamTypeToMethod(
        ClassMethod $classMethod,
        Param $param
    ): void {
        /** @var PhpDocInfo|null */
        $phpDocInfo = $classMethod->getAttribute(AttributeKey::PHP_DOC_INFO);
        if ($phpDocInfo === null) {
            $phpDocInfo = $this->phpDocInfoFactory->createEmpty($classMethod);
        }

        $paramName = $this->getName($param);
        $mappedCurrentParamType = $this->staticTypeMapper->mapPhpParserNodePHPStanType($param->type);
        $phpDocInfo->changeParamType($mappedCurrentParamType, $param, $paramName);
    }

    private function hasMethodWithTypedParam(string $parentClassName, string $methodName, string $paramName): bool
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
