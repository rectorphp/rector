<?php

declare (strict_types=1);
namespace Rector\DowngradePhp82\Rector\ArrowFunction;

use PhpParser\Node;
use PhpParser\Node\Expr\ArrowFunction;
use PHPStan\Type\NeverType;
use Rector\PhpDocDecorator\PhpDocFromTypeDeclarationDecorator;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * PHP 8.1 accepts the "never" return type everywhere except on arrow functions, where the implicit
 * return of the body expression trips "A never-returning function must not return" at compile time.
 * PHP 8.2 fixed that, so only the arrow-function case has to go when targeting 8.1; other
 * function-likes keep "never" until the 8.1 set removes it.
 *
 * @changelog https://github.com/php/php-src/issues/7900
 *
 * @see \Rector\Tests\DowngradePhp82\Rector\ArrowFunction\DowngradeArrowFunctionNeverReturnTypeRector\DowngradeArrowFunctionNeverReturnTypeRectorTest
 */
final class DowngradeArrowFunctionNeverReturnTypeRector extends AbstractRector
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
    public function getNodeTypes(): array
    {
        return [ArrowFunction::class];
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Remove "never" return type from arrow functions, which PHP 8.1 rejects at compile time', [new CodeSample(<<<'CODE_SAMPLE'
$callable = fn (): never => throw new \RuntimeException();
CODE_SAMPLE
, <<<'CODE_SAMPLE'
$callable = fn () => throw new \RuntimeException();
CODE_SAMPLE
)]);
    }
    /**
     * @param ArrowFunction $node
     */
    public function refactor(Node $node): ?Node
    {
        if (!$this->phpDocFromTypeDeclarationDecorator->decorateReturnWithSpecificType($node, new NeverType())) {
            return null;
        }
        return $node;
    }
}
