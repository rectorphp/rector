<?php

declare(strict_types=1);

namespace Rector\DowngradePhp72\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\ReflectionProvider;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Rector\DowngradePhp72\PhpDoc\NativeParamToPhpDocDecorator;
use Rector\NodeTypeResolver\Node\AttributeKey;
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
     * @var class-string[]
     */
    private array $safeTypes = [];

    /**
     * @var array<class-string, string[]>
     */
    private array $safeTypesToMethods = [];

    public function __construct(
        private NativeParamToPhpDocDecorator $nativeParamToPhpDocDecorator,
        private ReflectionProvider $reflectionProvider,
        private AutowiredClassMethodOrPropertyAnalyzer $autowiredClassMethodOrPropertyAnalyzer
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
        $classLike = $node->getAttribute(AttributeKey::CLASS_NODE);
        if ($classLike === null) {
            return null;
        }

        $className = $this->nodeNameResolver->getName($classLike);
        if ($className === null) {
            return null;
        }

        if (! $this->reflectionProvider->hasClass($className)) {
            return null;
        }

        $classReflection = $this->reflectionProvider->getClass($className);
        if ($this->isSealedClass($classReflection)) {
            return null;
        }

        if ($this->isSafeType($classReflection, $node)) {
            return null;
        }

        if ($node->isPrivate()) {
            return null;
        }

        if ($this->shouldSkipClassMethod($node)) {
            return null;
        }

        // Downgrade every scalar parameter, just to be sure
        foreach (array_keys($node->params) as $paramPosition) {
            $this->removeParamTypeFromMethod($node, $paramPosition);
        }

        return $node;
    }

    /**
     * @param array<string, mixed> $configuration
     */
    public function configure(array $configuration): void
    {
        $safeTypes = $configuration[self::SAFE_TYPES] ?? [];
        Assert::allString($safeTypes);
        $this->safeTypes = $safeTypes;

        $safeTypesToMethods = $configuration[self::SAFE_TYPES_TO_METHODS] ?? [];
        Assert::isArray($safeTypesToMethods);
        foreach ($safeTypesToMethods as $key => $value) {
            Assert::string($key);
            Assert::allString($value);
        }

        $this->safeTypesToMethods = $safeTypesToMethods;
    }

    private function removeParamTypeFromMethod(ClassMethod $classMethod, int $paramPosition): void
    {
        $param = $classMethod->params[$paramPosition] ?? null;
        if (! $param instanceof Param) {
            return;
        }

        // It already has no type => nothing to do - check original param, as it could have been removed by this rule
        if ($param->type === null) {
            return;
        }

        // Add the current type in the PHPDoc
        $this->nativeParamToPhpDocDecorator->decorate($classMethod, $param);
        $param->type = null;
    }

    private function shouldSkipClassMethod(ClassMethod $classMethod): bool
    {
        if ($classMethod->isMagic()) {
            return true;
        }

        if ($classMethod->params === []) {
            return true;
        }

        if ($this->autowiredClassMethodOrPropertyAnalyzer->detect($classMethod)) {
            return true;
        }

        foreach ($classMethod->params as $param) {
            if ($param->type !== null) {
                return false;
            }
        }

        return true;
    }

    /**
     * This method is perfectly sealed, nothing to downgrade here
     */
    private function isSealedClass(ClassReflection $classReflection): bool
    {
        if (! $classReflection->isClass()) {
            return false;
        }

        if (! $classReflection->isFinal()) {
            return false;
        }

        return count($classReflection->getAncestors()) === 1;
    }

    private function isSafeType(ClassReflection $classReflection, ClassMethod $classMethod): bool
    {
        foreach ($this->safeTypes as $safeType) {
            if ($classReflection->isSubclassOf($safeType)) {
                return true;
            }

            // skip self too
            if ($classReflection->getName() === $safeType) {
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
            if ($classReflection->getName() === $safeType) {
                return true;
            }
        }

        return false;
    }
}
