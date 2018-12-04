<?php declare(strict_types=1);

namespace Rector\Symfony\Tests\Rector\Form\FormIsValidRector;

use Rector\Symfony\Rector\Form\FormIsValidRector;
use Rector\Symfony\Tests\Rector\Form\FormIsValidRector\Source\Form;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class FormIsValidRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([__DIR__ . '/Wrong/wrong.php.inc']);
    }

    protected function getRectorClass(): string
    {
        return FormIsValidRector::class;
    }

    /**
     * @return mixed[]
     */
    protected function getRectorConfiguration(): array
    {
        return ['$formClass' => Form::class];
    }
}
