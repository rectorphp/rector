<?php declare(strict_types=1);

namespace Rector\ZendToSymfony\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Class_;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;
use Rector\Symfony\ValueObject\SymfonyClass;
use Rector\ZendToSymfony\Detector\ZendDetector;

/**
 * @sponsor Thanks https://previo.cz/ for sponsoring this rule
 *
 * @see \Rector\ZendToSymfony\Tests\Rector\Class_\ChangeZendControllerClassToSymfonyControllerClassRector\ChangeZendControllerClassToSymfonyControllerClassRectorTest
 */
final class ChangeZendControllerClassToSymfonyControllerClassRector extends AbstractRector
{
    /**
     * @var ZendDetector
     */
    private $zendDetector;

    public function __construct(ZendDetector $zendDetector)
    {
        $this->zendDetector = $zendDetector;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Change Zend 1 controller to Symfony 4 controller', [new CodeSample(
            <<<'PHP'
class SomeAction extends Zend_Controller_Action
{
}
PHP
            ,
            <<<'PHP'
final class SomeAction extends \Symfony\Bundle\FrameworkBundle\Controller\AbstractController
{
}
PHP
        )]);
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
        if (! $this->zendDetector->isInZendController($node)) {
            return null;
        }

        $node->extends = new FullyQualified(SymfonyClass::ABSTRACT_CONTROLLER);

        $this->makeFinal($node);

        return $node;
    }
}
