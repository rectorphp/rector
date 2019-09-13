<?php declare(strict_types=1);

namespace Rector\ZendToSymfony\Tests\Rector\Class_\ChangeZendControllerClassToSymfonyControllerClassRector;

use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\ZendToSymfony\Rector\Class_\ChangeZendControllerClassToSymfonyControllerClassRector;

final class ChangeZendControllerClassToSymfonyControllerClassRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideDataForTest()
     */
    public function test(string $file): void
    {
        $this->doTestFile($file);
    }

    /**
     * @return string[]
     */
    public function provideDataForTest(): iterable
    {
        yield [__DIR__ . '/Fixture/zend_controller.php.inc'];
    }

    protected function getRectorClass(): string
    {
        return ChangeZendControllerClassToSymfonyControllerClassRector::class;
    }
}
