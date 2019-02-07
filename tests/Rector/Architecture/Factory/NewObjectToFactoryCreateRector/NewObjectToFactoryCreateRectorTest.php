<?php declare(strict_types=1);

namespace Rector\Tests\Rector\Architecture\Factory\NewObjectToFactoryCreateRector;

use Rector\Rector\Architecture\Factory\NewObjectToFactoryCreateRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Tests\Rector\Architecture\Factory\NewObjectToFactoryCreateRector\Source\MyClass;
use Rector\Tests\Rector\Architecture\Factory\NewObjectToFactoryCreateRector\Source\MyClassFactory;

final class NewObjectToFactoryCreateRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([__DIR__ . '/Fixture/fixture.php.inc', __DIR__ . '/Fixture/fixture2.php.inc']);
    }

    protected function getRectorClass(): string
    {
        return NewObjectToFactoryCreateRector::class;
    }

    /**
     * @return mixed[]|null
     */
    protected function getRectorConfiguration(): ?array
    {
        return [
            MyClass::class => [
                'class' => MyClassFactory::class,
                'method' => 'create',
            ]
        ];
    }
}
