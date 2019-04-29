<?php declare(strict_types=1);

namespace Rector\NetteToSymfony\Tests\Rector\Class_\NetteFormToSymfonyFormRector;

use Rector\NetteToSymfony\Rector\Class_\NetteFormToSymfonyFormRector;
use Rector\NetteToSymfony\Tests\Rector\Class_\NetteFormToSymfonyFormRector\Source\NettePresenter;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class NetteFormToSymfonyFormRectorTest extends AbstractRectorTestCase
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
        return [NetteFormToSymfonyFormRector::class => [
            '$presenterClass' => NettePresenter::class,
        ]];
    }
}
