<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PHPStan\Analyser\Scope;
use Rector\Configuration\Deprecation\Contract\DeprecatedInterface;
use Rector\Contract\Rector\ConfigurableRectorInterface;
use Rector\Rector\AbstractScopeAwareRector;
use Rector\ValueObject\PhpVersion;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use RectorPrefix202407\Webmozart\Assert\Assert;
/**
 * @deprecated since 1.2.1, as duplicate of split rules. Use @see BoolReturnTypeFromStrictScalarReturnsRector, NumericReturnTypeFromStrictScalarReturnsRector, StringReturnTypeFromStrictScalarReturnsRector instead.
 */
final class ReturnTypeFromStrictScalarReturnExprRector extends AbstractScopeAwareRector implements MinPhpVersionInterface, ConfigurableRectorInterface, DeprecatedInterface
{
    /**
     * @api
     * @var string
     */
    public const HARD_CODED_ONLY = 'hard_coded_only';
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Change return type based on strict scalar returns - string, int, float or bool', [new ConfiguredCodeSample(<<<'CODE_SAMPLE'
final class SomeClass
{
    public function foo($value)
    {
        if ($value) {
            return 'yes';
        }

        return 'no';
    }

    public function bar(string $value)
    {
        return strlen($value);
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
final class SomeClass
{
    public function foo($value): string
    {
        if ($value) {
            return 'yes';
        }

        return 'no';
    }

    public function bar(string $value): int
    {
        return strlen($value);
    }
}
CODE_SAMPLE
, [self::HARD_CODED_ONLY => \false])]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [ClassMethod::class, Function_::class];
    }
    /**
     * @param ClassMethod|Function_ $node
     */
    public function refactorWithScope(Node $node, Scope $scope) : ?Node
    {
        // deprecated
        return null;
    }
    public function provideMinPhpVersion() : int
    {
        return PhpVersion::PHP_70;
    }
    public function configure(array $configuration) : void
    {
        $hardCodedOnly = $configuration[self::HARD_CODED_ONLY] ?? \false;
        Assert::boolean($hardCodedOnly);
    }
}
