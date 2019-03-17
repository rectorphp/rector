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

    protected function getRectorClass(): string
    {
        return NetteFormToSymfonyFormRector::class;
    }

    /**
     * @return mixed[]|null
     */
    protected function getRectorConfiguration(): ?array
    {
        return [
            '$presenterClass' => NettePresenter::class,
        ];
    }
}
