<?php declare(strict_types=1);

namespace Rector\Guzzle\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
 * @see https://github.com/guzzle/guzzle/blob/master/UPGRADING.md#updates-to-http-messages
 * @see https://github.com/guzzle/guzzle/commit/244bf0228e7e09740c9fb2d2fe77956a2c793eaa
 */
final class MessageAsArrayRector extends AbstractRector
{
    /**
     * @var string
     */
    private $messageType;

    public function __construct(string $messageType = 'GuzzleHttp\Message\MessageInterface')
    {
        $this->messageType = $messageType;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Changes getMessage(..., true) to getMessageAsArray()', [
            new CodeSample(
                <<<'CODE_SAMPLE'
/** @var GuzzleHttp\Message\MessageInterface */
$value = $message->getMessage('key', true);
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
/** @var GuzzleHttp\Message\MessageInterface */
$value = $message->getMessageAsArray('key');
CODE_SAMPLE
            ),
        ]);
    }

    /**
     * @return string[]
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
        if (! $this->isType($node, $this->messageType)) {
            return null;
        }

        if (! $this->isName($node, 'getMessage')) {
            return null;
        }

        if (! isset($node->args[1])) {
            return null;
        }

        if (! $this->isTrue($node->args[1]->value)) {
            return null;
        }

        // match!
        unset($node->args[1]);

        $node->name = new Identifier('getMessageAsArray');

        return $node;
    }
}
