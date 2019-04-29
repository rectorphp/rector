<?php declare(strict_types=1);

namespace Rector\Symfony\Tests\Rector\Form\FormIsValidRector;

use Rector\Symfony\Rector\Form\FormIsValidRector;
use Rector\Symfony\Tests\Rector\Form\FormIsValidRector\Source\Form;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class FormIsValidRectorTest extends AbstractRectorTestCase
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
        return [FormIsValidRector::class => ['$formClass' => Form::class]];
    }
}
