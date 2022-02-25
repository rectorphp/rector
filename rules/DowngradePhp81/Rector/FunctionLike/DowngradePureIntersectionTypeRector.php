<?php

declare (strict_types=1);
namespace Rector\DowngradePhp81\Rector\FunctionLike;

use PhpParser\Node;
use PhpParser\Node\Expr\ArrowFunction;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\IntersectionType;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use Rector\BetterPhpDocParser\PhpDocParser\PhpDocFromTypeDeclarationDecorator;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://wiki.php.net/rfc/pure-intersection-types
 *
 * @see \Rector\Tests\DowngradePhp81\Rector\FunctionLike\DowngradePureIntersectionTypeRector\DowngradePureIntersectionTypeRectorTest
 */
final class DowngradePureIntersectionTypeRector extends \Rector\Core\Rector\AbstractRector
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
        return [\PhpParser\Node\Expr\ArrowFunction::class, \PhpParser\Node\Stmt\ClassMethod::class, \PhpParser\Node\Expr\Closure::class, \PhpParser\Node\Stmt\Function_::class];
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Remove the intersection type params and returns, add @param/@return tags instead', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
function someFunction(): Foo&Bar
{
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
/**
 * @return Foo&Bar
 */
function someFunction()
{
}
CODE_SAMPLE
)]);
    }
    /**
     * @param ArrowFunction|ClassMethod|Closure|Function_ $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        $paramDecorated = \false;
        foreach ($node->getParams() as $param) {
            if (!$param->type instanceof \PhpParser\Node\IntersectionType) {
                continue;
            }
            $this->phpDocFromTypeDeclarationDecorator->decorateParam($param, $node, [\PHPStan\Type\IntersectionType::class]);
            $paramDecorated = \true;
        }
        if (!$node->returnType instanceof \PhpParser\Node\IntersectionType) {
            if ($paramDecorated) {
                return $node;
            }
            return null;
        }
        $this->phpDocFromTypeDeclarationDecorator->decorate($node);
        return $node;
    }
}
