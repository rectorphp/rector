<?php

declare(strict_types=1);

namespace Rector\DowngradePhp71\Rector\FunctionLike;

use PhpParser\Node\FunctionLike;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use Rector\DowngradePhp70\Rector\FunctionLike\AbstractDowngradeReturnDeclarationRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\DowngradePhp71\Tests\Rector\FunctionLike\DowngradeIterablePseudoTypeReturnDeclarationRector\DowngradeIterablePseudoTypeReturnDeclarationRectorTest
 */
final class DowngradeIterablePseudoTypeReturnDeclarationRector extends AbstractDowngradeReturnDeclarationRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Remove returning iterable pseud type, add a @return tag instead',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
class SomeClass
{
    public function run(): iterable
    {
        // do something
    }
}
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
class SomeClass
{
    /**
     * @return mixed[]|\Traversable
     */
    public function run()
    {
        // do something
    }
}
CODE_SAMPLE
                ),
            ]
        );
    }

    /**
     * @param ClassMethod|Function_ $functionLike
     */
    public function shouldRemoveReturnDeclaration(FunctionLike $functionLike): bool
    {
        $functionLikeReturnType = $functionLike->returnType;

        if ($functionLikeReturnType === null) {
            return false;
        }

        return $this->isName($functionLikeReturnType, 'iterable');
    }
}
