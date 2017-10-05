<?php declare(strict_types=1);

namespace Rector\Rector\Contrib\Symfony\Console;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Name\FullyQualified;
use Rector\Builder\Class_\ClassPropertyCollector;
use Rector\Builder\Kernel\ServiceFromKernelResolver;
use Rector\Builder\Naming\NameResolver;
use Rector\Node\Attribute;
use Rector\NodeAnalyzer\SymfonyContainerCallsAnalyzer;
use Rector\NodeFactory\NodeFactory;
use Rector\Rector\AbstractRector;
use Rector\Tests\Rector\Contrib\Symfony\HttpKernel\GetterToPropertyRector\Source\LocalKernel;

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
