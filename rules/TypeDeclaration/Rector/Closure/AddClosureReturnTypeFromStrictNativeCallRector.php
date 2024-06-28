<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\Rector\Closure;

use PhpParser\Node;
use PhpParser\Node\Expr\Closure;
use PHPStan\Analyser\Scope;
use Rector\Rector\AbstractScopeAwareRector;
use Rector\TypeDeclaration\NodeManipulator\AddReturnTypeFromStrictNativeCall;
use Rector\ValueObject\PhpVersion;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\TypeDeclaration\Rector\Closure\AddClosureReturnTypeFromStrictNativeCallRector\AddClosureReturnTypeFromStrictNativeCallRectorTest
 */
final class AddClosureReturnTypeFromStrictNativeCallRector extends AbstractScopeAwareRector implements MinPhpVersionInterface
{
    /**
     * @readonly
     * @var \Rector\TypeDeclaration\NodeManipulator\AddReturnTypeFromStrictNativeCall
     */
    private $addReturnTypeFromStrictNativeCall;
    public function __construct(AddReturnTypeFromStrictNativeCall $addReturnTypeFromStrictNativeCall)
    {
        $this->addReturnTypeFromStrictNativeCall = $addReturnTypeFromStrictNativeCall;
    }
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
        return $this->addReturnTypeFromStrictNativeCall->add($node, $scope);
    }
    public function provideMinPhpVersion() : int
    {
        return PhpVersion::PHP_70;
    }
}
