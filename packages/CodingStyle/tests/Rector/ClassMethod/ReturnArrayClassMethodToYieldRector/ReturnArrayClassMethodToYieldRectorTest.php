<?php declare(strict_types=1);

namespace Rector\CodingStyle\Tests\Rector\ClassMethod\ReturnArrayClassMethodToYieldRector;

use Rector\CodingStyle\Rector\ClassMethod\ReturnArrayClassMethodToYieldRector;
use Rector\CodingStyle\Tests\Rector\ClassMethod\ReturnArrayClassMethodToYieldRector\Source\EventSubscriberInterface;
use Rector\CodingStyle\Tests\Rector\ClassMethod\ReturnArrayClassMethodToYieldRector\Source\ParentTestCase;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class ReturnArrayClassMethodToYieldRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([
            __DIR__ . '/Fixture/fixture.php.inc',
            __DIR__ . '/Fixture/fixture2.php.inc',
            __DIR__ . '/Fixture/fixture3.php.inc',
            __DIR__ . '/Fixture/fixture4.php.inc',
            // @see https://github.com/FriendsOfPHP/PHP-CS-Fixer/commit/53fbbf01ca960f10c88acd0a38689c57bf3ade78
            __DIR__ . '/Fixture/data_provider.php.inc',
        ]);
    }

    protected function getRectorClass(): string
    {
        return ReturnArrayClassMethodToYieldRector::class;
    }

    /**
     * @return string[]
     */
    protected function getRectorConfiguration(): ?array
    {
        return [
            EventSubscriberInterface::class => ['getSubscribedEvents'],
            ParentTestCase::class => ['#(provide|dataProvider)*#'],
        ];
    }
}
