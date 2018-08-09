<?php declare(strict_types=1);

namespace Rector\Symfony\Rector\FrameworkBundle;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

final class GetToConstructorInjectionRector extends AbstractToConstructorInjectionRector
{
    /**
     * @var string
     */
    private $controllerClass;

    /**
     * @var string
     */
    private $traitClass;

    public function __construct(
        string $controllerClass = 'Symfony\Bundle\FrameworkBundle\Controller\Controller',
        string $traitClass = 'Symfony\Bundle\FrameworkBundle\Controller\ControllerTrait'
    ) {
        $this->controllerClass = $controllerClass;
        $this->traitClass = $traitClass;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Turns fetching of dependencies via `$this->get()` to constructor injection in Command and Controller in Symfony',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
class MyCommand extends ContainerAwareCommand
{
    public function someMethod()
    {
        // ...
        $this->get('some_service');
    }
}
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
class MyCommand extends Command
{
    public function __construct(SomeService $someService)
    {
        $this->someService = $someService;
    }

    public function someMethod()
    {
        $this->someService;
    }
}
CODE_SAMPLE
                ),
            ]
        );
    }

    public function isCandidate(Node $node): bool
    {
        if (! $node instanceof MethodCall) {
            return false;
        }

        if ($this->methodCallAnalyzer->isTypeAndMethod($node, $this->controllerClass, 'get')) {
            return true;
        }

        return $this->methodCallAnalyzer->isTypeAndMethod($node, $this->traitClass, 'get');
    }
}
