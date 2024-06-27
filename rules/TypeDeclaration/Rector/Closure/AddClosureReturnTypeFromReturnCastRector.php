<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\Rector\Closure;

use PhpParser\Node;
use PhpParser\Node\Expr\Closure;
use PHPStan\Analyser\Scope;
use Rector\Rector\AbstractScopeAwareRector;
use Rector\TypeDeclaration\NodeManipulator\AddReturnTypeFromCast;
use Rector\ValueObject\PhpVersionFeature;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\TypeDeclaration\Rector\Closure\AddClosureReturnTypeFromReturnCastRector\AddClosureReturnTypeFromReturnCastRectorTest
 */
final class AddClosureReturnTypeFromReturnCastRector extends AbstractScopeAwareRector implements MinPhpVersionInterface
{
    /**
     * @readonly
     * @var \Rector\TypeDeclaration\NodeManipulator\AddReturnTypeFromCast
     */
    private $addReturnTypeFromCast;
    public function __construct(AddReturnTypeFromCast $addReturnTypeFromCast)
    {
        $this->addReturnTypeFromCast = $addReturnTypeFromCast;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Add return type to closure with return cast', [new CodeSample(<<<'CODE_SAMPLE'
function ($param) {
    return (string) $param;
};
CODE_SAMPLE
, <<<'CODE_SAMPLE'
function ($param): string {
    return (string) $param;
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
        return $this->addReturnTypeFromCast->add($node, $scope);
    }
    public function provideMinPhpVersion() : int
    {
        return PhpVersionFeature::SCALAR_TYPES;
    }
}
