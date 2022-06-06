<?php

declare (strict_types=1);
namespace Rector\DowngradePhp81\Rector\FunctionLike;

use PhpParser\Node;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PHPStan\Type\NeverType;
use Rector\BetterPhpDocParser\PhpDocParser\PhpDocFromTypeDeclarationDecorator;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://wiki.php.net/rfc/noreturn_type
 *
 * @see \Rector\Tests\DowngradePhp81\Rector\FunctionLike\DowngradeNeverTypeDeclarationRector\DowngradeNeverTypeDeclarationRectorTest
 */
final class DowngradeNeverTypeDeclarationRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocParser\PhpDocFromTypeDeclarationDecorator
     */
    private $phpDocFromTypeDeclarationDecorator;
    public function __construct(\Rector\BetterPhpDocParser\PhpDocParser\PhpDocFromTypeDeclarationDecorator $phpDocFromTypeDeclarationDecorator)
    {
        $this->phpDocFromTypeDeclarationDecorator = $phpDocFromTypeDeclarationDecorator;
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Stmt\Function_::class, \PhpParser\Node\Stmt\ClassMethod::class, \PhpParser\Node\Expr\Closure::class];
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Remove "never" return type, add a "@return never" tag instead', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
function someFunction(): never
{
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
/**
 * @return never
 */
function someFunction()
{
}
CODE_SAMPLE
)]);
    }
    /**
     * @param ClassMethod|Closure|Function_ $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        $neverType = new \PHPStan\Type\NeverType();
        if (!$this->phpDocFromTypeDeclarationDecorator->decorateReturnWithSpecificType($node, $neverType)) {
            return null;
        }
        return $node;
    }
}
