<?php declare(strict_types=1);

namespace Rector\Rector\Contrib\Symfony\FrameworkBundle;

use PhpParser\Node;
use Rector\Node\Attribute;

/**
 * Ref: https://github.com/symfony/symfony/blob/master/UPGRADE-4.0.md#console
 *
 * Before:
 * class MyCommand extends ContainerAwareCommand
 * {
 *      // ...
 *      $this->getContainer()->get('some_service');
 *      $this->container->get('some_service');
 * }
 *
 * After:
 * class MyCommand extends Command
 * {
 *      public function __construct(SomeService $someService)
 *      {
 *          $this->someService = $someService;
 *      }
 *
 *      // ...
 *      $this->someService
 * }
 */
final class ContainerGetToConstructorInjectionRector extends AbstractToConstructorInjectionRector
{
    /**
     * @var string[]
     */
    private $containerAwareParentTypes = [
        'Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand',
        'Symfony\Bundle\FrameworkBundle\Controller\Controller',
    ];

    public function isCandidate(Node $node): bool
    {
        if (! $this->methodCallAnalyzer->isTypeAndMethod(
            $node,
            'Symfony\Component\DependencyInjection\ContainerInterface',
            'get'
        )) {
            return false;
        }

        $parentClassName = $node->getAttribute(Attribute::PARENT_CLASS_NAME);

        return in_array($parentClassName, $this->containerAwareParentTypes, true);
    }
}
