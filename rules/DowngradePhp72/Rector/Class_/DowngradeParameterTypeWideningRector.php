<?php

declare (strict_types=1);
namespace Rector\DowngradePhp72\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Interface_;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\NodeAnalyzer\ExternalFullyQualifiedAnalyzer;
use Rector\Core\Rector\AbstractRector;
use Rector\DowngradePhp72\NodeAnalyzer\ClassLikeWithTraitsClassMethodResolver;
use Rector\DowngradePhp72\NodeAnalyzer\ParamContravariantDetector;
use Rector\DowngradePhp72\NodeAnalyzer\ParentChildClassMethodTypeResolver;
use Rector\DowngradePhp72\PhpDoc\NativeParamToPhpDocDecorator;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\PHPStan\Type\TypeFactory;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://www.php.net/manual/en/migration72.new-features.php#migration72.new-features.param-type-widening
 * @see https://3v4l.org/fOgSE
 *
 * @see \Rector\Tests\DowngradePhp72\Rector\Class_\DowngradeParameterTypeWideningRector\DowngradeParameterTypeWideningRectorTest
 */
final class DowngradeParameterTypeWideningRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @var \Rector\DowngradePhp72\NodeAnalyzer\ClassLikeWithTraitsClassMethodResolver
     */
    private $classLikeWithTraitsClassMethodResolver;
    /**
     * @var \Rector\DowngradePhp72\NodeAnalyzer\ParentChildClassMethodTypeResolver
     */
    private $parentChildClassMethodTypeResolver;
    /**
     * @var \Rector\DowngradePhp72\PhpDoc\NativeParamToPhpDocDecorator
     */
    private $nativeParamToPhpDocDecorator;
    /**
     * @var \Rector\DowngradePhp72\NodeAnalyzer\ParamContravariantDetector
     */
    private $paramContravariantDetector;
    /**
     * @var \Rector\NodeTypeResolver\PHPStan\Type\TypeFactory
     */
    private $typeFactory;
    /**
     * @var \Rector\Core\NodeAnalyzer\ExternalFullyQualifiedAnalyzer
     */
    private $externalFullyQualifiedAnalyzer;
    public function __construct(\Rector\DowngradePhp72\NodeAnalyzer\ClassLikeWithTraitsClassMethodResolver $classLikeWithTraitsClassMethodResolver, \Rector\DowngradePhp72\NodeAnalyzer\ParentChildClassMethodTypeResolver $parentChildClassMethodTypeResolver, \Rector\DowngradePhp72\PhpDoc\NativeParamToPhpDocDecorator $nativeParamToPhpDocDecorator, \Rector\DowngradePhp72\NodeAnalyzer\ParamContravariantDetector $paramContravariantDetector, \Rector\NodeTypeResolver\PHPStan\Type\TypeFactory $typeFactory, \Rector\Core\NodeAnalyzer\ExternalFullyQualifiedAnalyzer $externalFullyQualifiedAnalyzer)
    {
        $this->classLikeWithTraitsClassMethodResolver = $classLikeWithTraitsClassMethodResolver;
        $this->parentChildClassMethodTypeResolver = $parentChildClassMethodTypeResolver;
        $this->nativeParamToPhpDocDecorator = $nativeParamToPhpDocDecorator;
        $this->paramContravariantDetector = $paramContravariantDetector;
        $this->typeFactory = $typeFactory;
        $this->externalFullyQualifiedAnalyzer = $externalFullyQualifiedAnalyzer;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Change param type to match the lowest type in whole family tree', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
interface SomeInterface
{
    public function test(array $input);
}

final class SomeClass implements SomeInterface
{
    public function test($input)
    {
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
interface SomeInterface
{
    /**
     * @param mixed[] $input
     */
    public function test($input);
}

final class SomeClass implements SomeInterface
{
    public function test($input)
    {
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Stmt\Class_::class, \PhpParser\Node\Stmt\Interface_::class];
    }
    /**
     * @param Class_|Interface_ $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        $scope = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::SCOPE);
        if (!$scope instanceof \PHPStan\Analyser\Scope) {
            return null;
        }
        $classReflection = $scope->getClassReflection();
        if (!$classReflection instanceof \PHPStan\Reflection\ClassReflection) {
            return null;
        }
        if ($this->isEmptyClassReflection($classReflection)) {
            return null;
        }
        if ($this->externalFullyQualifiedAnalyzer->hasExternalFullyQualifieds($node)) {
            return null;
        }
        $hasChanged = \false;
        /** @var ClassReflection[] $ancestors */
        $ancestors = $classReflection->getAncestors();
        $classMethods = $this->classLikeWithTraitsClassMethodResolver->resolve($ancestors);
        $classLikes = $this->nodeRepository->findClassesAndInterfacesByType($classReflection->getName());
        $interfaces = $classReflection->getInterfaces();
        foreach ($classMethods as $classMethod) {
            if ($this->skipClassMethod($classMethod, $classReflection, $ancestors, $classLikes)) {
                continue;
            }
            // refactor here
            $changedClassMethod = $this->refactorClassMethod($classMethod, $classReflection, $ancestors, $interfaces);
            if ($changedClassMethod !== null) {
                $hasChanged = \true;
            }
        }
        if ($hasChanged) {
            return $node;
        }
        return null;
    }
    /**
     * The topmost class is the source of truth, so we go only down to avoid up/down collission
     * @param ClassReflection[] $ancestors
     * @param ClassReflection[] $interfaces
     */
    private function refactorClassMethod(\PhpParser\Node\Stmt\ClassMethod $classMethod, \PHPStan\Reflection\ClassReflection $classReflection, array $ancestors, array $interfaces) : ?\PhpParser\Node\Stmt\ClassMethod
    {
        /** @var string $methodName */
        $methodName = $this->nodeNameResolver->getName($classMethod);
        $hasChanged = \false;
        foreach ($classMethod->params as $position => $param) {
            if (!\is_int($position)) {
                throw new \Rector\Core\Exception\ShouldNotHappenException();
            }
            // Resolve the types in:
            // - all ancestors + their descendant classes
            // @todo - all implemented interfaces + their implementing classes
            $parameterTypesByParentClassLikes = $this->parentChildClassMethodTypeResolver->resolve($classReflection, $methodName, $position, $ancestors, $interfaces);
            $uniqueTypes = $this->typeFactory->uniquateTypes($parameterTypesByParentClassLikes);
            if (!isset($uniqueTypes[1])) {
                continue;
            }
            $this->removeParamTypeFromMethod($classMethod, $param);
            $hasChanged = \true;
        }
        if ($hasChanged) {
            return $classMethod;
        }
        return null;
    }
    private function removeParamTypeFromMethod(\PhpParser\Node\Stmt\ClassMethod $classMethod, \PhpParser\Node\Param $param) : void
    {
        // It already has no type => nothing to do - check original param, as it could have been removed by this rule
        $originalParam = $param->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::ORIGINAL_NODE);
        if ($originalParam instanceof \PhpParser\Node\Param && $originalParam->type === null) {
            return;
        }
        if ($param->type === null) {
            return;
        }
        // Add the current type in the PHPDoc
        $this->nativeParamToPhpDocDecorator->decorate($classMethod, $param);
        $param->type = null;
    }
    /**
     * @param ClassReflection[] $ancestors
     * @param ClassLike[] $classLikes
     */
    private function skipClassMethod(\PhpParser\Node\Stmt\ClassMethod $classMethod, \PHPStan\Reflection\ClassReflection $classReflection, array $ancestors, array $classLikes) : bool
    {
        if ($classMethod->isMagic()) {
            return \true;
        }
        if ($classMethod->params === []) {
            return \true;
        }
        /** @var string $classMethodName */
        $classMethodName = $this->nodeNameResolver->getName($classMethod);
        if ($this->paramContravariantDetector->hasChildMethod($classLikes, $classMethodName)) {
            return \false;
        }
        return !$this->paramContravariantDetector->hasParentMethod($classReflection, $ancestors, $classMethodName);
    }
    private function isEmptyClassReflection(\PHPStan\Reflection\ClassReflection $classReflection) : bool
    {
        if ($classReflection->isInterface()) {
            return \false;
        }
        return \count($classReflection->getAncestors()) === 1;
    }
}
