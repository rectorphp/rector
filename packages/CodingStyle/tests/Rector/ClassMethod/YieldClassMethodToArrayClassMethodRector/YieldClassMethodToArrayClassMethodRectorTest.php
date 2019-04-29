<?php declare(strict_types=1);

namespace Rector\CodingStyle\Tests\Rector\ClassMethod\YieldClassMethodToArrayClassMethodRector;

use Rector\CodingStyle\Rector\ClassMethod\YieldClassMethodToArrayClassMethodRector;
use Rector\CodingStyle\Tests\Rector\ClassMethod\YieldClassMethodToArrayClassMethodRector\Source\EventSubscriberInterface;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class YieldClassMethodToArrayClassMethodRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([__DIR__ . '/Fixture/fixture.php.inc', __DIR__ . '/Fixture/type_declaration.php.inc']);
    }

    /**
     * @return mixed[]
     */
    protected function getRectorsWithConfiguration(): array
    {
        return [YieldClassMethodToArrayClassMethodRector::class => [
            '$methodsByType' => [
                EventSubscriberInterface::class => ['getSubscribedEvents'],
            ],
        ]];
    }
}
