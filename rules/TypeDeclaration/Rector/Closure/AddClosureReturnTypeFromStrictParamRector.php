<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\Rector\Closure;

use PhpParser\Node;
use PhpParser\Node\Expr\Closure;
use PHPStan\Analyser\Scope;
use Rector\Rector\AbstractScopeAwareRector;
use Rector\TypeDeclaration\NodeManipulator\AddReturnTypeFromParam;
use Rector\ValueObject\PhpVersionFeature;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\TypeDeclaration\Rector\Closure\AddClosureReturnTypeFromStrictParamRector\AddClosureReturnTypeFromStrictParamRectorTest
 */
final class AddClosureReturnTypeFromStrictParamRector extends AbstractScopeAwareRector implements MinPhpVersionInterface
{
    /**
     * @readonly
     * @var \Rector\TypeDeclaration\NodeManipulator\AddReturnTypeFromParam
     */
    private $addReturnTypeFromParam;
    public function __construct(AddReturnTypeFromParam $addReturnTypeFromParam)
    {
        $this->addReturnTypeFromParam = $addReturnTypeFromParam;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Add closure return type based on strict parameter type', [new CodeSample(<<<'CODE_SAMPLE'
function(ParamType $item)
{
    return $item;
};
CODE_SAMPLE
, <<<'CODE_SAMPLE'
function(ParamType $item): ParamType
{
    return $item;
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
        return $this->addReturnTypeFromParam->add($node, $scope);
    }
}
