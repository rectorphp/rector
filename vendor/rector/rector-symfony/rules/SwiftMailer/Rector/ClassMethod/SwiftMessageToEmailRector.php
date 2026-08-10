<?php

declare (strict_types=1);
namespace Rector\Symfony\SwiftMailer\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Configuration\Deprecation\Contract\DeprecatedInterface;
use Rector\Exception\ShouldNotHappenException;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @deprecated SwiftMailer and Symfony Mailer have different APIs, so the conversion is only a partial guess. The rule
 *             maps a handful of fluent calls and silently leaves the rest, which produces code that no longer sends
 *             the same email. Migrate to Symfony Mailer manually instead.
 */
final class SwiftMessageToEmailRector extends AbstractRector implements DeprecatedInterface
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Convert \Swift_Message into an \Symfony\Component\Mime\Email', [new CodeSample(<<<'CODE_SAMPLE'
$message = (new \Swift_Message('Hello Email'))
        ->setFrom('send@example.com')
        ->setTo(['recipient@example.com' => 'Recipient'])
        ->setBody(
            $this->renderView(
                'emails/registration.html.twig',
                ['name' => $name]
            ),
            'text/html'
        )
CODE_SAMPLE
, <<<'CODE_SAMPLE'
$message = (new Email())
    ->from(new Address('send@example.com'))
    ->to(new Address('recipient@example.com', 'Recipient'))
    ->subject('Hello Email')
    ->html($this->renderView(
        'emails/registration.html.twig',
        ['name' => $name]
    ))
;
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
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
        throw new ShouldNotHappenException(sprintf('"%s" is deprecated, as it only guesses a part of the SwiftMailer to Symfony Mailer conversion and leaves the rest behind. Migrate to Symfony Mailer manually instead.', self::class));
    }
}
