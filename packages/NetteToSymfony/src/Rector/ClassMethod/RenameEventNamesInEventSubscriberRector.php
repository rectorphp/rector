<?php declare(strict_types=1);

namespace Rector\NetteToSymfony\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Return_;
use Rector\NodeTypeResolver\Node\Attribute;
use Rector\PhpParser\Node\BetterNodeFinder;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
 * @see https://github.com/contributte/event-dispatcher-extra/blob/master/.docs/README.md#bridge-wrench
 * @see https://symfony.com/doc/current/reference/events.html
 */
final class RenameEventNamesInEventSubscriberRector extends AbstractRector
{
    /**
     * @var string[][]
     */
    private $netteStringNamesToSymfonyClassConstMap = [
        'nette.application.startup' => ['Symfony\Component\HttpKernel\KernelEvents', 'REQUEST'],
        'nette.application.shutdown' => ['Symfony\Component\HttpKernel\KernelEvents', 'TERMINATE'],
        'nette.application.request' => ['Symfony\Component\HttpKernel\KernelEvents', 'REQUEST'],
        'nette.application.presenter' => ['Symfony\Component\HttpKernel\KernelEvents', 'CONTROLLER'],
        'nette.application.presenter.startup' => ['Symfony\Component\HttpKernel\KernelEvents', 'CONTROLLER'],
        'nette.application.presenter.shutdown' => ['Symfony\Component\HttpKernel\KernelEvents', 'CONTROLLER'],
        'nette.application.response' => ['Symfony\Component\HttpKernel\KernelEvents', 'RESPONSE'],
        'nette.application.error' => ['Symfony\Component\HttpKernel\KernelEvents', 'EXCEPTION'],
    ];

    /**
     * @var string[][]
     */
    private $netteClassConstToSymfonyClassConstMap = [
        'Contributte\Events\Extra\Event\Application\StartupEvent::NAME' => [
            'Symfony\Component\HttpKernel\KernelEvents',
            'REQUEST',
        ],
        'Contributte\Events\Extra\Event\Application\PresenterShutdownEvent::NAME' => [
            'Symfony\Component\HttpKernel\KernelEvents',
            'TERMINATE',
        ],
        'Contributte\Events\Extra\Event\Application\RequestEvent::NAME' => [
            'Symfony\Component\HttpKernel\KernelEvents',
            'REQUEST',
        ],
        'Contributte\Events\Extra\Event\Application\PresenterEvent::NAME' => [
            'Symfony\Component\HttpKernel\KernelEvents',
            'CONTROLLER',
        ],
        'Contributte\Events\Extra\Event\Application\PresenterStartupEvent::NAME' => [
            'Symfony\Component\HttpKernel\KernelEvents',
            'CONTROLLER',
        ],
        'Contributte\Events\Extra\Event\Application\ResponseEvent::NAME' => [
            'Symfony\Component\HttpKernel\KernelEvents',
            'RESPONSE',
        ],
        'Contributte\Events\Extra\Event\Application\ErrorEvent::NAME' => [
            'Symfony\Component\HttpKernel\KernelEvents',
            'EXCEPTION',
        ],
        'Contributte\Events\Extra\Event\Application\ApplicationEvents::ON_STARTUP' => [
            'Symfony\Component\HttpKernel\KernelEvents',
            'REQUEST',
        ],
        'Contributte\Events\Extra\Event\Application\ApplicationEvents::ON_SHUTDOWN' => [
            'Symfony\Component\HttpKernel\KernelEvents',
            'TERMINATE',
        ],
        'Contributte\Events\Extra\Event\Application\ApplicationEvents::ON_REQUEST' => [
            'Symfony\Component\HttpKernel\KernelEvents',
            'REQUEST',
        ],
        'Contributte\Events\Extra\Event\Application\ApplicationEvents::ON_PRESENTER' => [
            'Symfony\Component\HttpKernel\KernelEvents',
            'CONTROLLER',
        ],
        'Contributte\Events\Extra\Event\Application\ApplicationEvents::ON_PRESENTER_STARTUP' => [
            'Symfony\Component\HttpKernel\KernelEvents',
            'CONTROLLER',
        ],
        'Contributte\Events\Extra\Event\Application\ApplicationEvents::ON_PRESENTER_SHUTDOWN' => [
            'Symfony\Component\HttpKernel\KernelEvents',
            'CONTROLLER',
        ],
        'Contributte\Events\Extra\Event\Application\ApplicationEvents::ON_RESPONSE' => [
            'Symfony\Component\HttpKernel\KernelEvents',
            'RESPONSE',
        ],
        'Contributte\Events\Extra\Event\Application\ApplicationEvents::ON_ERROR' => [
            'Symfony\Component\HttpKernel\KernelEvents',
            'EXCEPTION',
        ],
    ];

    /**
     * @var BetterNodeFinder
     */
    private $betterNodeFinder;

    public function __construct(BetterNodeFinder $betterNodeFinder)
    {
        $this->betterNodeFinder = $betterNodeFinder;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Changes event names from Nette ones to Symfony ones', [
            new CodeSample(
                <<<'CODE_SAMPLE'
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class SomeClass implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return ['nette.application' => 'someMethod'];
    }
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class SomeClass implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [\SymfonyEvents::KERNEL => 'someMethod'];
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
        return [ClassMethod::class];
    }

    /**
     * @param ClassMethod $node
     */
    public function refactor(Node $node): ?Node
    {
        $classNode = $node->getAttribute(Attribute::CLASS_NODE);

        if ($classNode === null) {
            return null;
        }

        if (! $this->isType($classNode, 'Symfony\Component\EventDispatcher\EventSubscriberInterface')) {
            return null;
        }

        if (! $this->isName($node, 'getSubscribedEvents')) {
            return null;
        }

        /** @var Return_[] $returnNodes */
        $returnNodes = $this->betterNodeFinder->findInstanceOf($node, Return_::class);

        foreach ($returnNodes as $returnNode) {
            if (! $returnNode->expr instanceof Array_) {
                continue;
            }

            $this->renameArrayKeys($returnNode);
        }

        return $node;
    }

    private function renameArrayKeys(Return_ $returnNode): void
    {
        if (! $returnNode->expr instanceof Array_) {
            return;
        }

        foreach ($returnNode->expr->items as $arrayItem) {
            $this->renameStringKeys($arrayItem);
            $this->renameClassConstKeys($arrayItem);
        }
    }

    private function renameStringKeys(ArrayItem $arrayItem): void
    {
        if (! $arrayItem->key instanceof String_) {
            return;
        }

        foreach ($this->netteStringNamesToSymfonyClassConstMap as $netteStringName => $symfonyClassConst) {
            if (! $this->isValue($arrayItem->key, $netteStringName)) {
                return;
            }

            $arrayItem->key = new ClassConstFetch(new FullyQualified(
                $symfonyClassConst[0]
            ), $symfonyClassConst[1]);

            return;
        }
    }

    private function renameClassConstKeys(ArrayItem $arrayItem): void
    {
        if (! $arrayItem->key instanceof ClassConstFetch) {
            return;
        }

        foreach ($this->netteClassConstToSymfonyClassConstMap as $netteClassConst => $symfonyClassConst) {
            $classConstFetchNode = $arrayItem->key;
            if (! $this->isName($classConstFetchNode, $netteClassConst)) {
                continue;
            }

            $arrayItem->key = new ClassConstFetch(new FullyQualified(
                $symfonyClassConst[0]
            ), $symfonyClassConst[1]);
        }
    }
}
