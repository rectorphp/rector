<?php declare(strict_types=1);

namespace Rector\Tests\Rector\ClassMethod\AddMethodParentCallRector;

use Rector\Rector\ClassMethod\AddMethodParentCallRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Tests\Rector\ClassMethod\AddMethodParentCallRector\Source\ParentClassWithNewConstructor;

final class AddMethodParentCallRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([__DIR__ . '/Fixture/fixture.php.inc', __DIR__ . '/Fixture/skip_already_has.php.inc']);
    }

    /**
     * @return mixed[]
     */
    protected function getRectorsWithConfiguration(): array
    {
        return [
            AddMethodParentCallRector::class => [
                '$methodsByParentTypes' => [
                    ParentClassWithNewConstructor::class => ['__construct'],
                ],
            ],
        ];
    }
}
