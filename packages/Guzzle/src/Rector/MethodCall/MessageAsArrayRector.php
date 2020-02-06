<?php

declare(strict_types=1);

namespace Rector\Guzzle\Rector\MethodCall;

use GuzzleHttp\Message\MessageInterface;
use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;

/**
 * @see https://github.com/guzzle/guzzle/blob/master/UPGRADING.md#updates-to-http-messages
 * @see https://github.com/guzzle/guzzle/commit/244bf0228e7e09740c9fb2d2fe77956a2c793eaa
 * @see \Rector\Guzzle\Tests\Rector\MethodCall\MessageAsArrayRector\MessageAsArrayRectorTest
 */
final class MessageAsArrayRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Changes getMessage(..., true) to getMessageAsArray()', [
            new CodeSample(
                <<<'PHP'
/** @var GuzzleHttp\Message\MessageInterface */
$value = $message->getMessage('key', true);
PHP
                ,
                <<<'PHP'
/** @var GuzzleHttp\Message\MessageInterface */
$value = $message->getMessageAsArray('key');
PHP
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
        if (! $this->isObjectType($node->var, MessageInterface::class)) {
            return null;
        }

        if (! $this->isName($node->name, 'getMessage')) {
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
