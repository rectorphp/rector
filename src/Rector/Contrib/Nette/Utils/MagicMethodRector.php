<?php declare(strict_types=1);

namespace Rector\Rector\Contrib\Nette\Utils;

use PhpParser\Node;
use Rector\Rector\AbstractRector;

/**
 * Covers @see https://github.com/RectorPHP/Rector/issues/49
 */
final class MagicMethodRector extends AbstractRector
{
    public function isCandidate(Node $node): bool
    {
        // class extending nette object
        // or using smart trait
        dump($node);
        die;
        // TODO: Implement isCandidate() method.
    }

    public function refactor(Node $node): ?Node
    {
        // TODO: Implement refactor() method.

        // read annotation
        // create public method
        // add it
    }
}
