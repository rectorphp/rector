<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use Rector\PHPStan\ScopeFetcher;
use Rector\Rector\AbstractRector;
use Rector\TypeDeclaration\NodeManipulator\AddReturnTypeFromCast;
use Rector\ValueObject\PhpVersionFeature;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromReturnCastRector\ReturnTypeFromReturnCastRectorTest
 */
final class ReturnTypeFromReturnCastRector extends AbstractRector implements MinPhpVersionInterface
{
    /**
     * @readonly
     */
    private AddReturnTypeFromCast $addReturnTypeFromCast;
    public function __construct(AddReturnTypeFromCast $addReturnTypeFromCast)
    {
        $this->addReturnTypeFromCast = $addReturnTypeFromCast;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Add return type to function like with return cast', [new CodeSample(<<<'CODE_SAMPLE'
final class SomeClass
{
    public function action($param)
    {
        try {
            return (array) $param;
        } catch (Exception $exception) {
            // some logging
            throw $exception;
        }
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
final class SomeClass
{
    public function action($param): array
    {
        try {
            return (array) $param;
        } catch (Exception $exception) {
            // some logging
            throw $exception;
        }
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
    /**
     * @param ClassMethod|Function_ $node
     */
    public function refactor(Node $node) : ?Node
    {
        $scope = ScopeFetcher::fetch($node);
        return $this->addReturnTypeFromCast->add($node, $scope);
    }
    public function provideMinPhpVersion() : int
    {
        return PhpVersionFeature::SCALAR_TYPES;
    }
}
