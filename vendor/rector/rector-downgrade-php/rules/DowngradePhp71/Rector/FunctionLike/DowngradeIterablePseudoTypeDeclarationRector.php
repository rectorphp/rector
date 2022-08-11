<?php

declare (strict_types=1);
namespace Rector\DowngradePhp71\Rector\FunctionLike;

use PhpParser\Node;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PHPStan\Type\IterableType;
use PHPStan\Type\MixedType;
use Rector\Core\Rector\AbstractRector;
use Rector\PhpDocDecorator\PhpDocFromTypeDeclarationDecorator;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://wiki.php.net/rfc/iterable
 *
 * @see \Rector\Tests\DowngradePhp71\Rector\FunctionLike\DowngradeIterablePseudoTypeDeclarationRector\DowngradeIterablePseudoTypeDeclarationRectorTest
 */
final class DowngradeIterablePseudoTypeDeclarationRector extends AbstractRector
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
        return new RuleDefinition('Remove the iterable pseudo type params and returns, add @param and @return tags instead', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run(iterable $iterator): iterable
    {
        // do something
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    /**
     * @param mixed[]|\Traversable $iterator
     * @return mixed[]|\Traversable
     */
    public function run($iterator)
    {
        // do something
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @param Function_|ClassMethod|Closure $node
     */
    public function refactor(Node $node) : ?Node
    {
        $iterableType = new IterableType(new MixedType(), new MixedType());
        foreach ($node->params as $param) {
            $this->phpDocFromTypeDeclarationDecorator->decorateParamWithSpecificType($param, $node, $iterableType);
        }
        if (!$this->phpDocFromTypeDeclarationDecorator->decorateReturnWithSpecificType($node, $iterableType)) {
            return null;
        }
        return $node;
    }
}
