<?php declare(strict_types=1);

namespace Rector\NetteToSymfony\Tests\Rector\MethodCall\WrapTransParameterNameRector;

use Rector\NetteToSymfony\Rector\MethodCall\WrapTransParameterNameRector;
use Rector\NetteToSymfony\Tests\Rector\MethodCall\WrapTransParameterNameRector\Source\SomeTranslator;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class WrapTransParameterNameRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([__DIR__ . '/Fixture/fixture.php.inc']);
    }

    protected function getRectorClass(): string
    {
        return WrapTransParameterNameRector::class;
    }

    protected function getRectorConfiguration(): ?array
    {
        return ['$translatorClass' => SomeTranslator::class];
    }
}
