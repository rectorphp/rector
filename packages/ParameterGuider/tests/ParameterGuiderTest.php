<?php declare(strict_types=1);

namespace Rector\ParameterGuider\Tests;

use PHPUnit\Framework\TestCase;
use Rector\DependencyInjection\ContainerFactory;
use Rector\ParameterGuider\Exception\ParameterTypoException;

final class ParameterGuiderTest extends TestCase
{
    public function test(): void
    {
        // the validation is triggered on command run
        $this->expectException(ParameterTypoException::class);

        (new ContainerFactory())->createWithConfigFiles([__DIR__ . '/config.yml']);
    }
}
