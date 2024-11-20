<?php

declare (strict_types=1);
namespace Rector\DowngradePhp72\Rector\FunctionLike;

use PhpParser\Node;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PHPStan\Type\ObjectWithoutClassType;
use Rector\PhpDocDecorator\PhpDocFromTypeDeclarationDecorator;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\DowngradePhp72\Rector\FunctionLike\DowngradeObjectTypeDeclarationRector\DowngradeObjectTypeDeclarationRectorTest
 */
final class DowngradeObjectTypeDeclarationRector extends AbstractRector
{
    /**
     * @readonly
     */
    private PhpDocFromTypeDeclarationDecorator $phpDocFromTypeDeclarationDecorator;
    public function __construct(PhpDocFromTypeDeclarationDecorator $phpDocFromTypeDeclarationDecorator)
    {
        $this->phpDocFromTypeDeclarationDecorator = $phpDocFromTypeDeclarationDecorator;
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [Function_::class, ClassMethod::class, Closure::class];
    }
    /**
     * @param Function_|ClassMethod $node
     */
    public function refactor(Node $node) : ?Node
    {
        $objectWithoutClassType = new ObjectWithoutClassType();
        $hasChanged = \false;
        $hasParamChanged = \false;
        foreach ($node->params as $param) {
            $hasParamChanged = $this->phpDocFromTypeDeclarationDecorator->decorateParamWithSpecificType($param, $node, $objectWithoutClassType);
            if ($hasParamChanged) {
                $hasChanged = \true;
            }
        }
        if (!$this->phpDocFromTypeDeclarationDecorator->decorateReturnWithSpecificType($node, $objectWithoutClassType)) {
            if ($hasChanged) {
                return $node;
            }
            return null;
        }
        return $node;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Remove the "object" param and return type, add a @param and @return tags instead', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function someFunction(object $someObject): object
    {
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    /**
     * @param object $someObject
     * @return object
     */
    public function someFunction($someObject)
    {
    }
}
CODE_SAMPLE
)]);
    }
}
