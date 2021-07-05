<?php

declare (strict_types=1);
namespace Rector\DowngradePhp72\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Rector\DowngradePhp72\PhpDoc\NativeParamToPhpDocDecorator;
use Rector\DowngradePhp72\PHPStan\ClassLikeScopeResolver;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use RectorPrefix20210705\Webmozart\Assert\Assert;
/**
 * @changelog https://www.php.net/manual/en/migration72.new-features.php#migration72.new-features.param-type-widening
 * @see https://3v4l.org/fOgSE
 *
 * @see \Rector\Tests\DowngradePhp72\Rector\ClassMethod\DowngradeParameterTypeWideningRector\DowngradeParameterTypeWideningRectorTest
 */
final class DowngradeParameterTypeWideningRector extends \Rector\Core\Rector\AbstractRector implements \Rector\Core\Contract\Rector\ConfigurableRectorInterface
{
    /**
     * @var string
     */
    public const SAFE_TYPES = 'safe_types';
    /**
     * @var class-string[]
     */
    private $safeTypes = [];
    /**
     * @var \Rector\DowngradePhp72\PhpDoc\NativeParamToPhpDocDecorator
     */
    private $nativeParamToPhpDocDecorator;
    /**
     * @var \Rector\DowngradePhp72\PHPStan\ClassLikeScopeResolver
     */
    private $classLikeScopeResolver;
    public function __construct(\Rector\DowngradePhp72\PhpDoc\NativeParamToPhpDocDecorator $nativeParamToPhpDocDecorator, \Rector\DowngradePhp72\PHPStan\ClassLikeScopeResolver $classLikeScopeResolver)
    {
        $this->nativeParamToPhpDocDecorator = $nativeParamToPhpDocDecorator;
        $this->classLikeScopeResolver = $classLikeScopeResolver;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Change param type to match the lowest type in whole family tree', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample(<<<'CODE_SAMPLE'
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
, [self::SAFE_TYPES => []])]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Stmt\ClassMethod::class];
    }
    /**
     * @param ClassMethod $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        $scope = $this->classLikeScopeResolver->resolveScope($node);
        if (!$scope instanceof \PHPStan\Analyser\Scope) {
            return null;
        }
        $classReflection = $scope->getClassReflection();
        if (!$classReflection instanceof \PHPStan\Reflection\ClassReflection) {
            return null;
        }
        if ($this->isSealedClass($classReflection)) {
            return null;
        }
        foreach ($this->safeTypes as $safeType) {
            if ($classReflection->isSubclassOf($safeType)) {
                return null;
            }
        }
        if ($this->skipClassMethod($node)) {
            return null;
        }
        // Downgrade every scalar parameter, just to be sure
        foreach (\array_keys($node->params) as $paramPosition) {
            $this->removeParamTypeFromMethod($node, $paramPosition);
        }
        return $node;
    }
    /**
     * @param array<string, class-string[]> $configuration
     */
    public function configure(array $configuration) : void
    {
        $safeTypes = $configuration[self::SAFE_TYPES] ?? [];
        \RectorPrefix20210705\Webmozart\Assert\Assert::allString($safeTypes);
        $this->safeTypes = $safeTypes;
    }
    private function removeParamTypeFromMethod(\PhpParser\Node\Stmt\ClassMethod $classMethod, int $paramPosition) : void
    {
        $param = $classMethod->params[$paramPosition] ?? null;
        if (!$param instanceof \PhpParser\Node\Param) {
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
    private function skipClassMethod(\PhpParser\Node\Stmt\ClassMethod $classMethod) : bool
    {
        if ($classMethod->isMagic()) {
            return \true;
        }
        return $classMethod->params === [];
    }
    /**
     * This method is perfectly sealed, nothing to downgrade here
     */
    private function isSealedClass(\PHPStan\Reflection\ClassReflection $classReflection) : bool
    {
        if (!$classReflection->isClass()) {
            return \false;
        }
        if (!$classReflection->isFinal()) {
            return \false;
        }
        return \count($classReflection->getAncestors()) === 1;
    }
}
