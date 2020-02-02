<?php

declare(strict_types=1);

namespace Rector\CakePHPToSymfony\Rector\Class_;

use Nette\Utils\FileSystem;
use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use Rector\CakePHPToSymfony\NodeFactory\EventSubscriberClassFactory;
use Rector\CakePHPToSymfony\Rector\AbstractCakePHPRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
 * @inspired by
 * @see \Rector\NetteToSymfony\Rector\Assign\FormControlToControllerAndFormTypeRector::refactor()
 *
 * @see \Rector\CakePHPToSymfony\Tests\Rector\Class_\CakePHPBeforeFilterToRequestEventSubscriberRector\CakePHPBeforeFilterToRequestEventSubscriberRectorExtraTest
 */
final class CakePHPBeforeFilterToRequestEventSubscriberRector extends AbstractCakePHPRector
{
    /**
     * @var EventSubscriberClassFactory
     */
    private $eventSubscriberClassFactory;

    public function __construct(EventSubscriberClassFactory $eventSubscriberClassFactory)
    {
        $this->eventSubscriberClassFactory = $eventSubscriberClassFactory;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Migrate CakePHP beforeFilter() method from controller to Event Subscriber before request',
            [
                new CodeSample(
                    <<<'PHP'
class SuperadminController extends \AppController
{
    public function beforeFilter()
    {
    	// something
    }
}
PHP
,
                    <<<'PHP'
class SuperadminController extends \AppController
{
}
PHP

                ),
            ]
        );
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [Class_::class];
    }

    /**
     * @param Class_ $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $this->isInCakePHPController($node)) {
            return null;
        }

        $beforeFilterClassMethod = $node->getMethod('beforeFilter');
        if ($beforeFilterClassMethod === null) {
            return null;
        }

        $this->removeNode($beforeFilterClassMethod);

        // create event subscriber with name...
        $eventSubscriberClass = $this->eventSubscriberClassFactory->createEventSubscriberClass(
            $node,
            $beforeFilterClassMethod
        );
        $eventSubscriberFilePath = $this->eventSubscriberClassFactory->resolveEventSubscriberFilePath($node);

        // @todo make temporary
        $content = '<?php' . PHP_EOL . $this->print($eventSubscriberClass) . PHP_EOL;
        FileSystem::write($eventSubscriberFilePath, $content);

        return null;
    }
}
