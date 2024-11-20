<?php

declare (strict_types=1);
namespace Rector\DowngradePhp81\Rector\FunctionLike;

use PhpParser\Node;
use PhpParser\Node\Expr\ArrowFunction;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\IntersectionType;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use Rector\PhpDocDecorator\PhpDocFromTypeDeclarationDecorator;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://wiki.php.net/rfc/pure-intersection-types
 *
 * @see \Rector\Tests\DowngradePhp81\Rector\FunctionLike\DowngradePureIntersectionTypeRector\DowngradePureIntersectionTypeRectorTest
 */
final class DowngradePureIntersectionTypeRector extends AbstractRector
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
        return [ArrowFunction::class, ClassMethod::class, Closure::class, Function_::class];
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Remove the intersection type params and returns, add @param/@return tags instead', [new CodeSample(<<<'CODE_SAMPLE'
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
    public function refactor(Node $node) : ?Node
    {
        $paramDecorated = \false;
        foreach ($node->getParams() as $param) {
            if (!$param->type instanceof IntersectionType) {
                continue;
            }
            $this->phpDocFromTypeDeclarationDecorator->decorateParam($param, $node, [\PHPStan\Type\IntersectionType::class]);
            $paramDecorated = \true;
        }
        if (!$node->returnType instanceof IntersectionType) {
            if ($paramDecorated) {
                return $node;
            }
            return null;
        }
        $this->phpDocFromTypeDeclarationDecorator->decorateReturn($node);
        return $node;
    }
}
