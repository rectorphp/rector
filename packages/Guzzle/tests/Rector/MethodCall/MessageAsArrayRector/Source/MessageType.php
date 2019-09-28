<?php declare(strict_types=1);

namespace Rector\Guzzle\Tests\Rector\MethodCall\MessageAsArrayRector\Source;

use GuzzleHttp\Message\MessageInterface;

class MessageType implements MessageInterface
{
    public function getMessage()
    {
    }
}
