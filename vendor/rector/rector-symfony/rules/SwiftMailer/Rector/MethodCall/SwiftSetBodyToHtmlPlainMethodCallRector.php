<?php

declare (strict_types=1);
namespace Rector\Symfony\SwiftMailer\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use Rector\Configuration\Deprecation\Contract\DeprecatedInterface;
use Rector\Exception\ShouldNotHappenException;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @deprecated The renamed html()/text() methods only exist on Symfony Mailer Email objects, but the rule renames the
 *             call while the object is still a Swift_Message. Migrate to Symfony Mailer manually instead.
 */
final class SwiftSetBodyToHtmlPlainMethodCallRector extends AbstractRector implements DeprecatedInterface
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Changes setBody() method call on Swift_Message into a html() or plain() based on second argument', [new CodeSample(<<<'CODE_SAMPLE'
$message = new Swift_Message();

$message->setBody('...', 'text/html');

$message->setBody('...', 'text/plain');
$message->setBody('...');
CODE_SAMPLE
, <<<'CODE_SAMPLE'
$message = new Swift_Message();

$message->html('...');

$message->text('...');
$message->text('...');
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
        throw new ShouldNotHappenException(sprintf('"%s" is deprecated, as it renames the call to methods that only exist on a Symfony Mailer Email, while the object is still a Swift_Message. Migrate to Symfony Mailer manually instead.', self::class));
    }
}
