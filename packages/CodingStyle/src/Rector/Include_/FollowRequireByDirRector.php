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
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;

/**
 * @see \Rector\CodingStyle\Tests\Rector\Include_\FollowRequireByDirRector\FollowRequireByDirRectorTest
 */
final class FollowRequireByDirRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('include/require should be followed by absolute path', [
            new CodeSample(
                <<<'PHP'
class SomeClass
{
    public function run()
    {
        require 'autoload.php';
    }
}
PHP
                ,
                <<<'PHP'
class SomeClass
{
    public function run()
    {
        require __DIR__ . '/autoload.php';
    }
}
PHP
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
        // nothing we can do
        if (! $node->expr instanceof String_) {
            return null;
        }

        $includedPath = $node->expr;
        if (Strings::startsWith($includedPath->value, 'phar://')) {
            return null;
        }

        $this->removeExtraDotSlash($includedPath);
        $this->prependSlashIfMissing($includedPath);

        $node->expr = new Concat(new Dir(), $includedPath);

        return $node;
    }

    /**
     * Remove "./" which would break the path
     */
    private function removeExtraDotSlash(String_ $includedPath): void
    {
        if (! Strings::startsWith($includedPath->value, './')) {
            return;
        }

        $includedPath->value = Strings::replace($includedPath->value, '#^\.\/#', '/');
    }

    private function prependSlashIfMissing(String_ $includedPath): void
    {
        if (Strings::startsWith($includedPath->value, '/')) {
            return;
        }

        $includedPath->value = '/' . $includedPath->value;
    }
}
