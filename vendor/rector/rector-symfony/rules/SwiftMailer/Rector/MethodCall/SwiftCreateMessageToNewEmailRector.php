<?php

declare (strict_types=1);
namespace Rector\Symfony\SwiftMailer\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use Rector\Configuration\Deprecation\Contract\DeprecatedInterface;
use Rector\Exception\ShouldNotHappenException;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @deprecated The Swift_Mailer::createMessage() argument is dropped on the way to a bare Email object, so the
 *             produced message loses its content. Migrate to Symfony Mailer manually instead.
 */
final class SwiftCreateMessageToNewEmailRector extends AbstractRector implements DeprecatedInterface
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Changes createMessage() into a new Symfony\Component\Mime\Email', [new CodeSample(<<<'CODE_SAMPLE'
$email = $this->swift->createMessage('message');
CODE_SAMPLE
, <<<'CODE_SAMPLE'
$email = new \Symfony\Component\Mime\Email();
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
        throw new ShouldNotHappenException(sprintf('"%s" is deprecated, as it drops the createMessage() argument and produces an empty Email object. Migrate to Symfony Mailer manually instead.', self::class));
    }
}
