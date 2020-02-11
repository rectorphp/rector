<?php

declare(strict_types=1);

namespace Rector\ZendToSymfony\Rector\Expression;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Return_;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\ZendToSymfony\Detector\ZendDetector;

/**
 * Before:
 * return $redirector->gotoUrl(PROJECT_URL);
 *
 * After:
 * return $this->redirect(PROJECT_URL);
 *
 * @see https://symfony.com/doc/current/controller.html#redirecting
 *
 * @sponsor Thanks https://previo.cz/ for sponsoring this rule
 *
 * @see \Rector\ZendToSymfony\Tests\Rector\Expression\RedirectorToRedirectToUrlRector\RedirectorToRedirectToUrlRectorTest
 */
final class RedirectorToRedirectToUrlRector extends AbstractRector
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
        return new RectorDefinition('Change $redirector helper to Symfony\Controller call redirect()', [
            new CodeSample(
                <<<'PHP'
public function someAction()
{
    $redirector = $this->_helper->redirector;
    $redirector->goToUrl('abc');
}
PHP
                ,
                <<<'PHP'
public function someAction()
{
    $redirector = $this->_helper->redirector;
    $this->redirect('abc');
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
        return [Expression::class, Return_::class];
    }

    /**
     * @param Expression|Return_ $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $this->zendDetector->isInZendController($node)) {
            return null;
        }

        $possibleRedirector = $node->expr;
        if (! $possibleRedirector instanceof MethodCall) {
            return null;
        }

        // @todo better check type in the future with custom PHPStan type extension
        if (! $possibleRedirector->var instanceof Variable) {
            return null;
        }

        if (! $this->isName($possibleRedirector->var, 'redirector')) {
            return null;
        }

        if (! $this->isName($possibleRedirector->name, 'goToUrl')) {
            return null;
        }

        $redirectToUrlMethodCall = new MethodCall(new Variable('this'), 'redirect', $possibleRedirector->args);

        if ($node instanceof Return_) {
            $node->expr = $redirectToUrlMethodCall;
            return $node;
        }

        return new Return_($redirectToUrlMethodCall);
    }
}
