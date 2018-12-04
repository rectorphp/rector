<?php declare(strict_types=1);

namespace Rector\Guzzle\Tests\Rector\MethodCall\MessageAsArrayRector;

use Rector\Guzzle\Rector\MethodCall\MessageAsArrayRector;
use Rector\Guzzle\Tests\Rector\MethodCall\MessageAsArrayRector\Source\MessageType;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class MessageAsArrayRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([__DIR__ . '/Wrong/wrong.php.inc']);
    }

    protected function getRectorClass(): string
    {
        return MessageAsArrayRector::class;
    }

    /**
     * @return mixed[]
     */
    protected function getRectorConfiguration(): array
    {
        return ['$messageType' => MessageType::class];
    }
}
