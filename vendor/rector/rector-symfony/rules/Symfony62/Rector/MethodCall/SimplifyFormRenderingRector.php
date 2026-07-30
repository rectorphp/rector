<?php

declare (strict_types=1);
namespace Rector\Symfony\Symfony62\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use Rector\Configuration\Deprecation\Contract\DeprecatedInterface;
use Rector\Exception\ShouldNotHappenException;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @deprecated Passing the form instead of `->createView()` is ambiguous and breaks dynamic forms, e.g. AJAX submits of forms modified in event listeners end up with a 422 response. Keep the explicit `->createView()` call.
 * @see https://github.com/symfony/symfony/issues/50542
 */
final class SimplifyFormRenderingRector extends AbstractRector implements DeprecatedInterface
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Symplify form rendering by not calling `->createView()` on `render` function', [new CodeSample(<<<'CODE_SAMPLE'
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ReplaceFormCreateViewFunctionCall extends AbstractController
{
    public function form(): Response
    {
        return $this->render('form.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ReplaceFormCreateViewFunctionCall extends AbstractController
{
    public function form(): Response
    {
        return $this->render('form.html.twig', [
            'form' => $form,
        ]);
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
        return [MethodCall::class];
    }
    /**
     * @param MethodCall $node
     */
    public function refactor(Node $node): ?Node
    {
        throw new ShouldNotHappenException(sprintf('"%s" is deprecated, as passing the form instead of "->createView()" is ambiguous and breaks dynamic forms. Keep the explicit "->createView()" call. See https://github.com/symfony/symfony/issues/50542', self::class));
    }
}
