<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use Rector\PHPStan\ScopeFetcher;
use Rector\Rector\AbstractRector;
use Rector\TypeDeclaration\NodeManipulator\AddReturnTypeFromParam;
use Rector\ValueObject\PhpVersionFeature;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromStrictParamRector\ReturnTypeFromStrictParamRectorTest
 */
final class ReturnTypeFromStrictParamRector extends AbstractRector implements MinPhpVersionInterface
{
    /**
     * @readonly
     */
    private AddReturnTypeFromParam $addReturnTypeFromParam;
    public function __construct(AddReturnTypeFromParam $addReturnTypeFromParam)
    {
        $this->addReturnTypeFromParam = $addReturnTypeFromParam;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Add return type based on strict parameter type', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function resolve(ParamType $item)
    {
        return $item;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function resolve(ParamType $item): ParamType
    {
        return $item;
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [ClassMethod::class, Function_::class];
    }
    public function provideMinPhpVersion() : int
    {
        return PhpVersionFeature::NULLABLE_TYPE;
    }
    /**
     * @param ClassMethod|Function_ $node
     */
    public function refactor(Node $node) : ?Node
    {
        $scope = ScopeFetcher::fetch($node);
        return $this->addReturnTypeFromParam->add($node, $scope);
    }
}
