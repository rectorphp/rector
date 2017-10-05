<?php declare(strict_types=1);

namespace Rector\Rector\Contrib\Symfony\Console;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use Rector\Rector\AbstractRector;

/**
 * Ref:
 * - https://github.com/symfony/symfony/pull/22441/files
 * - https://github.com/symfony/symfony/blob/master/UPGRADE-3.3.md#console
 *
 * Before:
 * - "console.exception"
 * or
 * - Symfony\Component\Console\ConsoleEvents::EXCEPTION
 *
 * After:
 * - Symfony\Component\Console\ConsoleEvents::ERROR
 *
 * ---
 *
 * Before:
 * - Symfony\Component\Console\Event\ConsoleExceptionEvent
 *
 * After:
 * - Symfony\Component\Console\Event\ConsoleErrorEvent
 */
final class ConsoleExceptionEventToErrorEventRector extends AbstractRector
{
    public function isCandidate(Node $node): bool
    {
        // rename event -> ClassConstantAnalyzer Class::OLD => Class::NEW
        // rename class occurande
    }

    /**
     * @param MethodCall $node
     */
    public function refactor(Node $node): ?Node
    {
        dump($node);
        die;
    }
}
