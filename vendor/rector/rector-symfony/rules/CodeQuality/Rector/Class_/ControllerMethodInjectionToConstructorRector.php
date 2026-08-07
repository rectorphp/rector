<?php

declare (strict_types=1);
namespace Rector\Symfony\CodeQuality\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use Rector\Configuration\Deprecation\Contract\DeprecatedInterface;
use Rector\Exception\ShouldNotHappenException;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @deprecated Method injection is a valid Symfony feature, not a smell. Moving an action-scoped service to the constructor
 *             is opinionated and makes the code less readable - the action no longer states what it needs, and controllers
 *             with a parent constructor even gain an extra #[Required] autowire method.
 */
final class ControllerMethodInjectionToConstructorRector extends AbstractRector implements DeprecatedInterface
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Change Symfony controller method injection to direct constructor dependency, to separate params and services clearly. If a parent class has a constructor, use #[Required] autowire<ShortClassName>() method instead, to avoid repeating all parent params', [new CodeSample(<<<'CODE_SAMPLE'
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

final class SomeController extends AbstractController
{
    #[Route('/some-path', name: 'some_name')]
    public function someAction(
        Request $request,
        SomeService $someService
    ) {
        $data = $someService->getData();
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

final class SomeController extends AbstractController
{
    public function __construct(
        private readonly SomeService $someService
    ) {
    }

    #[Route('/some-path', name: 'some_name')]
    public function someAction(
        Request $request
    ) {
        $data = $this->someService->getData();
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
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
        throw new ShouldNotHappenException(sprintf('"%s" is deprecated, as constructor injection of action-scoped services is opinionated and makes controllers less readable. Keep the method injection.', self::class));
    }
}
