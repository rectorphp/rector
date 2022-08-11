<?php

declare (strict_types=1);
namespace Rector\DowngradePhp70\Rector\FunctionLike;

use PhpParser\Node;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PHPStan\Type\ObjectType;
use Rector\Core\Rector\AbstractRector;
use Rector\PhpDocDecorator\PhpDocFromTypeDeclarationDecorator;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\DowngradePhp70\Rector\FunctionLike\DowngradeThrowableTypeDeclarationRector\DowngradeThrowableTypeDeclarationRectorTest
 */
final class DowngradeThrowableTypeDeclarationRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\PhpDocDecorator\PhpDocFromTypeDeclarationDecorator
     */
    private $phpDocFromTypeDeclarationDecorator;
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
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Replace `Throwable` type hints by PHPDoc tags', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function foo(\Throwable $e): ?\Throwable
    {
        return new \Exception("Troubles");
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    /**
     * @param \Throwable $e
     * @return \Throwable|null
     */
    public function foo($e)
    {
        return new \Exception("Troubles");
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @param ClassMethod|Function_|Closure $node
     */
    public function refactor(Node $node) : ?Node
    {
        $objectType = new ObjectType('Throwable');
        foreach ($node->getParams() as $param) {
            $this->phpDocFromTypeDeclarationDecorator->decorateParamWithSpecificType($param, $node, $objectType);
        }
        if (!$this->phpDocFromTypeDeclarationDecorator->decorateReturnWithSpecificType($node, $objectType)) {
            return null;
        }
        return $node;
    }
}
