<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\Rector\Closure;

use PhpParser\Node;
use PhpParser\Node\Expr\Closure;
use PHPStan\Analyser\Scope;
use Rector\Rector\AbstractScopeAwareRector;
use Rector\TypeDeclaration\NodeManipulator\AddUnionReturnType;
use Rector\ValueObject\PhpVersionFeature;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\TypeDeclaration\Rector\Closure\AddClosureUnionReturnTypeRector\AddClosureUnionReturnTypeRectorTest
 */
final class AddClosureUnionReturnTypeRector extends AbstractScopeAwareRector implements MinPhpVersionInterface
{
    /**
     * @readonly
     * @var \Rector\TypeDeclaration\NodeManipulator\AddUnionReturnType
     */
    private $addUnionReturnType;
    public function __construct(AddUnionReturnType $addUnionReturnType)
    {
        $this->addUnionReturnType = $addUnionReturnType;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Add union return type on closure', [new CodeSample(<<<'CODE_SAMPLE'
function () {
    if (rand(0, 1)) {
        return 1;
    }

    return 'one';
};
CODE_SAMPLE
, <<<'CODE_SAMPLE'
function (): int|string {
    if (rand(0, 1)) {
        return 1;
    }

    return 'one';
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
    public function provideMinPhpVersion() : int
    {
        return PhpVersionFeature::NULLABLE_TYPE;
    }
    /**
     * @param Closure $node
     */
    public function refactorWithScope(Node $node, Scope $scope) : ?Node
    {
        return $this->addUnionReturnType->add($node, $scope);
    }
}
