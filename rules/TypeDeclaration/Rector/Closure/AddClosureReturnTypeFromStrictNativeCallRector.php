<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\Rector\Closure;

use PhpParser\Node;
use PhpParser\Node\Expr\Closure;
use PHPStan\Analyser\Scope;
use Rector\Configuration\Deprecation\Contract\DeprecatedInterface;
use Rector\Rector\AbstractScopeAwareRector;
use Rector\ValueObject\PhpVersion;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @deprecated since 1.2.1, use @see \Rector\TypeDeclaration\Rector\Closure\ClosureReturnTypeRector instead
 */
final class AddClosureReturnTypeFromStrictNativeCallRector extends AbstractScopeAwareRector implements MinPhpVersionInterface, DeprecatedInterface
{
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Add closure strict return type based native function or native method', [new CodeSample(<<<'CODE_SAMPLE'
function () {
    $dt = new DateTime('now');
    return $dt->format('Y-m-d');
};
CODE_SAMPLE
, <<<'CODE_SAMPLE'
function (): string {
    $dt = new DateTime('now');
    return $dt->format('Y-m-d');
};
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [Closure::class];
    }
    /**
     * @param Closure $node
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
}
