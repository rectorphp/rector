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

    public function __construct(ChildParamPopulator $childParamPopulator, ParamTypeInferer $paramTypeInferer)
    {
        $this->paramTypeInferer = $paramTypeInferer;
        $this->childParamPopulator = $childParamPopulator;
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
        // dump($param->type);
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
        // dump('---', $methodName, $paramName);
        // Obtain the list of the ancestors with a different signature
        $refactorableAncestorClasses = [];
        var_dump('---', $classReflection->getParentClassesNames());
        foreach ($classReflection->getParentClassesNames() as $parentClassName) {
            var_dump('++', $parentClassName, $methodName, method_exists($parentClassName, $methodName));
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
            var_dump('++', $interfaceClassName, $methodName, method_exists($interfaceClassName, $methodName));
            if (! method_exists($interfaceClassName, $methodName)) {
                continue;
            }

            if ($this->hasMethodWithTypedParam($classReflection, $interfaceClassName, $methodName, $paramName)) {
                $refactorableInterfaceClasses[] = $interfaceClassName;
            }
        }

        // Remove the types in all ancestors, and in their descendant classes
        foreach ($refactorableAncestorClasses as $ancestorClass) {
            dump($ancestorClass);
            $ancestorClassNode = $this->nodeRepository->findClass($ancestorClass);
            // $ancestorClassNode->type = null;
            $childrenClassLikes = $this->nodeRepository->findClassesAndInterfacesByType($ancestorClass);
            foreach ($childrenClassLikes as $childrenClassLike) {
                // $this->childParamPopulator->populateChildClassMethod($ancestorClassNode, $position, null);
                //
            }
        }
        foreach ($refactorableInterfaceClasses as $interfaceClass) {
            // dump('***', $interfaceClass, $this->nodeRepository->findClassMethod($interfaceClass, $methodName));
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

                    /** @var Node */
                    $paramType = $methodParam->type;
                    $type = $this->staticTypeMapper->mapPhpParserNodePHPStanType($paramType);
                    $phpDocInfo->changeParamType($type, $methodParam, $paramName);

                    $methodParam->type = null;

                    break;
                }
            }
            $childrenClassLikes = $this->nodeRepository->findClassesAndInterfacesByType($interfaceClass);
            foreach ($childrenClassLikes as $childrenClassLike) {
                // $this->childParamPopulator->populateChildClassMethod($ancestorClassNode, $position, null);
                //
            }
        }
    }

    private function hasMethodWithTypedParam(ClassReflection $classReflection, string $parentClassName, string $methodName, string $paramName): bool
    {
        $parentReflectionMethod = new ReflectionMethod($parentClassName, $methodName);
        /** @var ReflectionParameter[] */
        $parentReflectionMethodParams = $parentReflectionMethod->getParameters();
        foreach ($parentReflectionMethodParams as $reflectionParameter) {
            dump($reflectionParameter->name, $reflectionParameter->getType());
            if ($reflectionParameter->name === $paramName && $reflectionParameter->getType() !== null) {
                return true;
            }
        }

        return false;
    }
}
