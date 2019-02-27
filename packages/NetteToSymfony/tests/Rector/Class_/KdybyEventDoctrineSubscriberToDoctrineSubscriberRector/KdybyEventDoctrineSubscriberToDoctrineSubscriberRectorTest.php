<?php declare(strict_types=1);

namespace Rector\NetteToSymfony\Tests\Rector\Class_\KdybyEventDoctrineSubscriberToDoctrineSubscriberRector;

use Rector\NetteToSymfony\Rector\Class_\KdybyEventDoctrineSubscriberToDoctrineSubscriberRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class KdybyEventDoctrineSubscriberToDoctrineSubscriberRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([__DIR__ . '/Fixture/fixture.php.inc']);
    }

    protected function getRectorClass(): string
    {
        return KdybyEventDoctrineSubscriberToDoctrineSubscriberRector::class;
    }
}
