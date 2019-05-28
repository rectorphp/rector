<?php declare(strict_types=1);

namespace Rector\Symfony\Tests\Rector\Form\FormTypeGetParentRector;

use Rector\Symfony\Rector\Form\FormTypeGetParentRector;
use Rector\Symfony\Tests\Rector\Form\FormTypeGetParentRector\Source\AbstractType;
use Rector\Symfony\Tests\Rector\Form\FormTypeGetParentRector\Source\AbstractTypeExtension;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class FormTypeGetParentRectorTest extends AbstractRectorTestCase
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
        return [
            FormTypeGetParentRector::class => [
                '$abstractTypeClass' => AbstractType::class,
                '$abstractTypeExtensionClass' => AbstractTypeExtension::class,
            ],
        ];
    }
}
