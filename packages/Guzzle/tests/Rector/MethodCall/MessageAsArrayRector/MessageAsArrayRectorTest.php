<?php declare(strict_types=1);

namespace Rector\Guzzle\Tests\Rector\MethodCall\MessageAsArrayRector;

use Rector\Guzzle\Rector\MethodCall\MessageAsArrayRector;
use Rector\Guzzle\Tests\Rector\MethodCall\MessageAsArrayRector\Source\MessageType;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class MessageAsArrayRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([__DIR__ . '/Fixture/fixture.php.inc']);
    }

    /**
     * @return mixed[]
     */
    protected function getRectorsWithConfiguration(): array
    {
        return [
            MessageAsArrayRector::class => [
                '$messageType' => MessageType::class,
            ],
        ];
    }
}
