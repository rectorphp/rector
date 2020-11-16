<?php

declare(strict_types=1);

namespace Rector\CodingStyle\Rector\Include_;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp\Concat;
use PhpParser\Node\Expr\Include_;
use PhpParser\Node\Scalar\MagicConst\Dir;
use PhpParser\Node\Scalar\String_;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\CodingStyle\Tests\Rector\Include_\FollowRequireByDirRector\FollowRequireByDirRectorTest
 */
final class FollowRequireByDirRector extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'include/require should be followed by absolute path',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        require 'autoload.php';
    }
}
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        require __DIR__ . '/autoload.php';
    }
}
CODE_SAMPLE
                ),

            ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [Include_::class];
    }

    /**
     * @param Include_ $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($node->expr instanceof Concat && $node->expr->left instanceof String_ && $this->isRefactorableStringPath(
            $node->expr->left
        )) {
            $node->expr->left = $this->prefixWithDir($node->expr->left);

            return $node;
        }

        if ($node->expr instanceof String_ && $this->isRefactorableStringPath($node->expr)) {
            $node->expr = $this->prefixWithDir($node->expr);

            return $node;
        }
        // nothing we can do
        return null;
    }

    private function isRefactorableStringPath(String_ $string): bool
    {
        return ! Strings::startsWith($string->value, 'phar://');
    }

    private function prefixWithDir(String_ $string): Concat
    {
        $this->removeExtraDotSlash($string);
        $this->prependSlashIfMissing($string);

        return new Concat(new Dir(), $string);
    }

    /**
     * Remove "./" which would break the path
     */
    private function removeExtraDotSlash(String_ $string): void
    {
        if (! Strings::startsWith($string->value, './')) {
            return;
        }

        $string->value = Strings::replace($string->value, '#^\.\/#', '/');
    }

    private function prependSlashIfMissing(String_ $string): void
    {
        if (Strings::startsWith($string->value, '/')) {
            return;
        }

        $string->value = '/' . $string->value;
    }
}
