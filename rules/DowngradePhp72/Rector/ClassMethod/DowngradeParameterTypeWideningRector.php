<?php

declare(strict_types=1);

namespace Rector\DowngradePhp72\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Reflection\ClassReflection;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\Reflection\ReflectionResolver;
use Rector\DowngradePhp72\NodeAnalyzer\BuiltInMethodAnalyzer;
use Rector\DowngradePhp72\NodeAnalyzer\OverrideFromAnonymousClassMethodAnalyzer;
use Rector\DowngradePhp72\NodeAnalyzer\SealedClassAnalyzer;
use Rector\DowngradePhp72\PhpDoc\NativeParamToPhpDocDecorator;
use Rector\TypeDeclaration\NodeAnalyzer\AutowiredClassMethodOrPropertyAnalyzer;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use Webmozart\Assert\Assert;

/**
 * @changelog https://www.php.net/manual/en/migration72.new-features.php#migration72.new-features.param-type-widening
 * @see https://3v4l.org/fOgSE
 *
 * @see \Rector\Tests\DowngradePhp72\Rector\ClassMethod\DowngradeParameterTypeWideningRector\DowngradeParameterTypeWideningRectorTest
 */
final class DowngradeParameterTypeWideningRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @var string
     */
    public const SAFE_TYPES = 'safe_types';

    /**
     * @var string
     */
    public const SAFE_TYPES_TO_METHODS = 'safe_types_to_methods';

    /**
     * @var string[]
     */
    private array $safeTypes = [];

    /**
     * @var array<string, string[]>
     */
    private array $safeTypesToMethods = [];

    public function __construct(
        private NativeParamToPhpDocDecorator $nativeParamToPhpDocDecorator,
        private ReflectionResolver $reflectionResolver,
        private AutowiredClassMethodOrPropertyAnalyzer $autowiredClassMethodOrPropertyAnalyzer,
        private BuiltInMethodAnalyzer $builtInMethodAnalyzer,
        private OverrideFromAnonymousClassMethodAnalyzer $overrideFromAnonymousClassMethodAnalyzer,
        private SealedClassAnalyzer $sealedClassAnalyzer
    ) {
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Change param type to match the lowest type in whole family tree', [
            new ConfiguredCodeSample(
                <<<'CODE_SAMPLE'
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
                ,
                <<<'CODE_SAMPLE'
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
            ,
                [
                    self::SAFE_TYPES => [],
                    self::SAFE_TYPES_TO_METHODS => [],
                ]
            ),
        ]);
    }

    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [ClassMethod::class];
    }

    /**
     * @param ClassMethod $node
     */
    public function refactor(Node $node): ?Node
    {
        $classLike = $this->betterNodeFinder->findParentType($node, ClassLike::class);
        if (! $classLike instanceof ClassLike) {
            return null;
        }

        $ancestorOverridableAnonymousClass = $this->overrideFromAnonymousClassMethodAnalyzer->matchAncestorClassReflectionOverrideable(
            $classLike,
            $node
        );

        if ($ancestorOverridableAnonymousClass instanceof ClassReflection) {
            return $this->processRemoveParamTypeFromMethod($ancestorOverridableAnonymousClass, $node);
        }

        $classReflection = $this->reflectionResolver->resolveClassAndAnonymousClass($classLike);
        if (! $classReflection instanceof ClassReflection) {
            throw new ShouldNotHappenException();
        }

        return $this->processRemoveParamTypeFromMethod($classReflection, $node);
    }

    /**
     * @param array<string, mixed> $configuration
     */
    public function configure(array $configuration): void
    {
        $safeTypes = $configuration[self::SAFE_TYPES] ?? ($configuration ?: []);
        Assert::isArray($safeTypes);
        Assert::allString($safeTypes);
        $this->safeTypes = $safeTypes;

        $safeTypesToMethods = $configuration[self::SAFE_TYPES_TO_METHODS] ?? ($configuration ?: []);
        Assert::isArray($safeTypesToMethods);
        foreach ($safeTypesToMethods as $key => $value) {
            Assert::string($key);
            Assert::allString($value);
        }

        $this->safeTypesToMethods = $safeTypesToMethods;
    }

    private function shouldSkip(ClassReflection $classReflection, ClassMethod $classMethod): bool
    {
        if ($classMethod->params === []) {
            return true;
        }

        if ($classMethod->isPrivate()) {
            return true;
        }

        if ($classMethod->isMagic()) {
            return true;
        }

        if ($this->sealedClassAnalyzer->isSealedClass($classReflection)) {
            return true;
        }

        if ($this->autowiredClassMethodOrPropertyAnalyzer->detect($classMethod)) {
            return true;
        }

        if ($this->hasParamAlreadyNonTyped($classMethod)) {
            return true;
        }

        return $this->isSafeType($classReflection, $classMethod);
    }

    private function processRemoveParamTypeFromMethod(
        ClassReflection $classReflection,
        ClassMethod $classMethod
    ): ?ClassMethod {
        if ($this->shouldSkip($classReflection, $classMethod)) {
            return null;
        }

        if ($this->builtInMethodAnalyzer->isImplementsBuiltInInterface($classReflection, $classMethod)) {
            return null;
        }

        // Downgrade every scalar parameter, just to be sure
        foreach (array_keys($classMethod->params) as $paramPosition) {
            $this->removeParamTypeFromMethod($classMethod, $paramPosition);
        }

        return $classMethod;
    }

    private function removeParamTypeFromMethod(ClassMethod $classMethod, int $paramPosition): void
    {
        $param = $classMethod->params[$paramPosition] ?? null;
        if (! $param instanceof Param) {
            return;
        }

        // Add the current type in the PHPDoc
        $this->nativeParamToPhpDocDecorator->decorate($classMethod, $param);
        $param->type = null;
    }

    private function hasParamAlreadyNonTyped(ClassMethod $classMethod): bool
    {
        foreach ($classMethod->params as $param) {
            if ($param->type !== null) {
                return false;
            }
        }

        return true;
    }

    private function isSafeType(ClassReflection $classReflection, ClassMethod $classMethod): bool
    {
        $classReflectionName = $classReflection->getName();
        foreach ($this->safeTypes as $safeType) {
            if ($classReflection->isSubclassOf($safeType)) {
                return true;
            }

            // skip self too
            if ($classReflectionName === $safeType) {
                return true;
            }
        }

        foreach ($this->safeTypesToMethods as $safeType => $safeMethods) {
            if (! $this->isNames($classMethod, $safeMethods)) {
                continue;
            }

            if ($classReflection->isSubclassOf($safeType)) {
                return true;
            }

            // skip self too
            if ($classReflectionName === $safeType) {
                return true;
            }
        }

        return false;
    }
}
