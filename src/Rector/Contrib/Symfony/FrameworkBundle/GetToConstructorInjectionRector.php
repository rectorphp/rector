<?php declare(strict_types=1);

namespace Rector\Rector\Contrib\Symfony\FrameworkBundle;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;

/**
 * Before:
 * - $this->get('some_service') # where "some_service" is name of the service in container.
 *
 * After:
 * - $this->someService # where "someService" is type of the service
 */
final class GetToConstructorInjectionRector extends AbstractToConstructorInjectionRector
{
    public function isCandidate(Node $node): bool
    {
        if (! $node instanceof MethodCall) {
            return false;
        }

        return $this->methodCallAnalyzer->isTypeAndMethod(
            $node,
            'Symfony\Bundle\FrameworkBundle\Controller\Controller',
            'get'
        );
    }
}
