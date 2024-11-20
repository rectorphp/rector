<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use Rector\PHPStan\ScopeFetcher;
use Rector\Rector\AbstractRector;
use Rector\TypeDeclaration\NodeManipulator\AddUnionReturnType;
use Rector\ValueObject\PhpVersionFeature;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\TypeDeclaration\Rector\ClassMethod\ReturnUnionTypeRector\ReturnUnionTypeRectorTest
 */
final class ReturnUnionTypeRector extends AbstractRector implements MinPhpVersionInterface
{
    /**
     * @readonly
     */
    private AddUnionReturnType $addUnionReturnType;
    public function __construct(AddUnionReturnType $addUnionReturnType)
    {
        $this->addUnionReturnType = $addUnionReturnType;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Add union return type', [new CodeSample(<<<'CODE_SAMPLE'
final class SomeClass
{
    public function getData()
    {
        if (rand(0, 1)) {
            return null;
        }

        if (rand(0, 1)) {
            return new DateTime('now');
        }

        return new stdClass;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
final class SomeClass
{
    public function getData(): null|\DateTime|\stdClass
    {
        if (rand(0, 1)) {
            return null;
        }

        if (rand(0, 1)) {
            return new DateTime('now');
        }

        return new stdClass;
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
        return PhpVersionFeature::UNION_TYPES;
    }
    /**
     * @param ClassMethod|Function_ $node
     */
    public function refactor(Node $node) : ?Node
    {
        $scope = ScopeFetcher::fetch($node);
        return $this->addUnionReturnType->add($node, $scope);
    }
}
