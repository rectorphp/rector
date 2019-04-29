<?php declare(strict_types=1);

namespace Rector\Symfony\Tests\Rector\MethodCall\ReadOnlyOptionToAttributeRector;

use Rector\Symfony\Rector\MethodCall\ReadOnlyOptionToAttributeRector;
use Rector\Symfony\Tests\Rector\MethodCall\ReadOnlyOptionToAttributeRector\Source\FormBuilder;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class ReadOnlyOptionToAttributeRectorTest extends AbstractRectorTestCase
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
        return [ReadOnlyOptionToAttributeRector::class => ['$formBuilderType' => FormBuilder::class]];
    }
}
