<?php declare(strict_types=1);

namespace Rector\Symfony\Tests\Rector\Form\OptionNameRector;

use Rector\Symfony\Rector\Form\OptionNameRector;
use Rector\Symfony\Tests\Rector\Form\OptionNameRector\Source\FormBuilder;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class OptionNameRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([__DIR__ . '/Fixture/fixture.php.inc', __DIR__ . '/Fixture/fixture2.php.inc']);
    }

    /**
     * @return mixed[]
     */
    protected function getRectorsWithConfiguration(): array
    {
        return [OptionNameRector::class => [
            '$formBuilderType' => FormBuilder::class,
        ]];
    }
}
