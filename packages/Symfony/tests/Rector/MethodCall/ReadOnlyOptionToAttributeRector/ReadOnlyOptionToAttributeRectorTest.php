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

    protected function getRectorClass(): string
    {
        return ReadOnlyOptionToAttributeRector::class;
    }

    /**
     * @return mixed[]
     */
    protected function getRectorConfiguration(): array
    {
        return ['$formBuilderType' => FormBuilder::class];
    }
}
