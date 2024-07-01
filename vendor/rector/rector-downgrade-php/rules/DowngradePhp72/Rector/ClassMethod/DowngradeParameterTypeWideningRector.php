<?php

declare (strict_types=1);
namespace Rector\DowngradePhp72\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Reflection\ClassReflection;
use Rector\Contract\Rector\ConfigurableRectorInterface;
use Rector\DowngradePhp72\NodeAnalyzer\BuiltInMethodAnalyzer;
use Rector\DowngradePhp72\NodeAnalyzer\OverrideFromAnonymousClassMethodAnalyzer;
use Rector\DowngradePhp72\NodeAnalyzer\SealedClassAnalyzer;
use Rector\DowngradePhp72\PhpDoc\NativeParamToPhpDocDecorator;
use Rector\Rector\AbstractRector;
use Rector\Reflection\ReflectionResolver;
use Rector\TypeDeclaration\NodeAnalyzer\AutowiredClassMethodOrPropertyAnalyzer;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use RectorPrefix202407\Webmozart\Assert\Assert;
/**
 * @changelog https://www.php.net/manual/en/migration72.new-features.php#migration72.new-features.param-type-widening
 * @changelog https://3v4l.org/fOgSE
 *
 * @see \Rector\Tests\DowngradePhp72\Rector\ClassMethod\DowngradeParameterTypeWideningRector\DowngradeParameterTypeWideningRectorTest
 */
final class DowngradeParameterTypeWideningRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @readonly
     * @var \Rector\DowngradePhp72\PhpDoc\NativeParamToPhpDocDecorator
     */
    private $nativeParamToPhpDocDecorator;
    /**
     * @readonly
     * @var \Rector\Reflection\ReflectionResolver
     */
    private $reflectionResolver;
    /**
     * @readonly
     * @var \Rector\TypeDeclaration\NodeAnalyzer\AutowiredClassMethodOrPropertyAnalyzer
     */
    private $autowiredClassMethodOrPropertyAnalyzer;
    /**
     * @readonly
     * @var \Rector\DowngradePhp72\NodeAnalyzer\BuiltInMethodAnalyzer
     */
    private $builtInMethodAnalyzer;
    /**
     * @readonly
     * @var \Rector\DowngradePhp72\NodeAnalyzer\OverrideFromAnonymousClassMethodAnalyzer
     */
    private $overrideFromAnonymousClassMethodAnalyzer;
    /**
     * @readonly
     * @var \Rector\DowngradePhp72\NodeAnalyzer\SealedClassAnalyzer
     */
    private $sealedClassAnalyzer;
    /**
     * @var array<string, string[]>
     */
    private $unsafeTypesToMethods = [];
    public function __construct(NativeParamToPhpDocDecorator $nativeParamToPhpDocDecorator, ReflectionResolver $reflectionResolver, AutowiredClassMethodOrPropertyAnalyzer $autowiredClassMethodOrPropertyAnalyzer, BuiltInMethodAnalyzer $builtInMethodAnalyzer, OverrideFromAnonymousClassMethodAnalyzer $overrideFromAnonymousClassMethodAnalyzer, SealedClassAnalyzer $sealedClassAnalyzer)
    {
        $this->nativeParamToPhpDocDecorator = $nativeParamToPhpDocDecorator;
        $this->reflectionResolver = $reflectionResolver;
        $this->autowiredClassMethodOrPropertyAnalyzer = $autowiredClassMethodOrPropertyAnalyzer;
        $this->builtInMethodAnalyzer = $builtInMethodAnalyzer;
        $this->overrideFromAnonymousClassMethodAnalyzer = $overrideFromAnonymousClassMethodAnalyzer;
        $this->sealedClassAnalyzer = $sealedClassAnalyzer;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Change param type to match the lowest type in whole family tree', [new ConfiguredCodeSample(<<<'CODE_SAMPLE'
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
, ['ContainerInterface' => ['set', 'get', 'has', 'initialized'], 'SomeContainerInterface' => ['set', 'has']])]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [ClassLike::class];
    }
    /**
     * @param ClassLike $node
     */
    public function refactor(Node $node) : ?Node
    {
        $hasChanged = \false;
        foreach ($node->getMethods() as $method) {
            $ancestorOverridableAnonymousClass = $this->overrideFromAnonymousClassMethodAnalyzer->matchAncestorClassReflectionOverrideable($node, $method);
            if ($ancestorOverridableAnonymousClass instanceof ClassReflection) {
                $classMethod = $this->processRemoveParamTypeFromMethod($ancestorOverridableAnonymousClass, $method);
                if ($classMethod instanceof ClassMethod) {
                    $hasChanged = \true;
                    continue;
                }
                continue;
            }
            $classReflection = $this->reflectionResolver->resolveClassAndAnonymousClass($node);
            $classMethod = $this->processRemoveParamTypeFromMethod($classReflection, $method);
            if ($classMethod instanceof ClassMethod) {
                $hasChanged = \true;
            }
        }
        if ($hasChanged) {
            return $node;
        }
        return null;
    }
    /**
     * @param mixed[] $configuration
     */
    public function configure(array $configuration) : void
    {
        $unsafeTypesToMethods = $configuration;
        foreach ($unsafeTypesToMethods as $key => $value) {
            Assert::string($key);
            Assert::allString($value);
        }
        $this->unsafeTypesToMethods = $unsafeTypesToMethods;
    }
    private function shouldSkip(ClassReflection $classReflection, ClassMethod $classMethod) : bool
    {
        if ($classMethod->params === []) {
            return \true;
        }
        if ($classMethod->isPrivate()) {
            return \true;
        }
        if ($classMethod->isMagic()) {
            return \true;
        }
        if ($this->sealedClassAnalyzer->isSealedClass($classReflection)) {
            return \true;
        }
        if ($this->autowiredClassMethodOrPropertyAnalyzer->detect($classMethod)) {
            return \true;
        }
        if ($this->hasParamAlreadyNonTyped($classMethod)) {
            return \true;
        }
        return $this->isSafeType($classReflection, $classMethod);
    }
    private function processRemoveParamTypeFromMethod(ClassReflection $classReflection, ClassMethod $classMethod) : ?ClassMethod
    {
        if ($this->shouldSkip($classReflection, $classMethod)) {
            return null;
        }
        if ($this->builtInMethodAnalyzer->isImplementsBuiltInInterface($classReflection, $classMethod)) {
            return null;
        }
        // Downgrade every scalar parameter, just to be sure
        foreach (\array_keys($classMethod->params) as $paramPosition) {
            $this->removeParamTypeFromMethod($classMethod, $paramPosition);
        }
        return $classMethod;
    }
    private function removeParamTypeFromMethod(ClassMethod $classMethod, int $paramPosition) : void
    {
        $param = $classMethod->params[$paramPosition] ?? null;
        if (!$param instanceof Param) {
            return;
        }
        // Add the current type in the PHPDoc
        $this->nativeParamToPhpDocDecorator->decorate($classMethod, $param);
        $param->type = null;
    }
    private function hasParamAlreadyNonTyped(ClassMethod $classMethod) : bool
    {
        foreach ($classMethod->params as $param) {
            if ($param->type !== null) {
                return \false;
            }
        }
        return \true;
    }
    private function isSafeType(ClassReflection $classReflection, ClassMethod $classMethod) : bool
    {
        if ($this->unsafeTypesToMethods === []) {
            return \false;
        }
        $classReflectionName = $classReflection->getName();
        foreach ($this->unsafeTypesToMethods as $unsafeType => $unsafeMethods) {
            if (!$this->isNames($classMethod, $unsafeMethods)) {
                continue;
            }
            if ($classReflection->isSubclassOf($unsafeType)) {
                return \false;
            }
            // skip self too
            if ($classReflectionName === $unsafeType) {
                return \false;
            }
        }
        return \true;
    }
}
