<?php declare(strict_types=1);

namespace Rector\Symfony\Tests\Rector\Form\StringFormTypeToClassRector;

use Rector\Symfony\Rector\Form\StringFormTypeToClassRector;
use Rector\Symfony\Tests\Rector\Form\StringFormTypeToClassRector\Source\FormBuilder;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class StringFormTypeToClassRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([__DIR__ . '/Wrong/wrong.php.inc', __DIR__ . '/Wrong/wrong2.php.inc']);
    }

    protected function getRectorClass(): string
    {
        return StringFormTypeToClassRector::class;
    }

    /**
     * @return mixed[]
     */
    protected function getRectorConfiguration(): array
    {
        return ['$formBuilderClass' => FormBuilder::class];
    }
}
