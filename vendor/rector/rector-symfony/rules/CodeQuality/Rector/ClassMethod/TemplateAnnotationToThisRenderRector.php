<?php

declare (strict_types=1);
namespace Rector\Symfony\CodeQuality\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use Rector\Configuration\Deprecation\Contract\DeprecatedInterface;
use Rector\Exception\ShouldNotHappenException;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see https://github.com/symfony/symfony-docs/pull/12387#discussion_r329551967
 * @see https://symfony.com/doc/current/bundles/SensioFrameworkExtraBundle/annotations/view.html
 *
 * @deprecated Too risky to run blindly, as the template name is guessed from bundle and controller naming conventions,
 *             and every return has to be classified as template variables or an already built Response. Both depend on
 *             project-specific conventions. Write a local custom rule tailored to your own controllers instead.
 */
final class TemplateAnnotationToThisRenderRector extends AbstractRector implements DeprecatedInterface
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Turns `@Template` annotation to explicit method call in Controller of FrameworkExtraBundle in Symfony', [new CodeSample(<<<'CODE_SAMPLE'
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

final class SomeController
{
    /**
     * @Template()
     */
    public function indexAction()
    {
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

final class SomeController
{
    public function indexAction()
    {
        return $this->render('index.html.twig');
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
        throw new ShouldNotHappenException(sprintf('"%s" is deprecated, as it guesses the template name from naming conventions and has to classify every return as template variables or a Response. Write a local custom rule for your own controllers instead.', self::class));
    }
}
