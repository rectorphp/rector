<?php declare(strict_types=1);

namespace Rector\Tests\Naming;

use PHPUnit\Framework\TestCase;
use Rector\Naming\CommandNaming;

final class CommandNamingTest extends TestCase
{
    public function test(): void
    {
        $name = CommandNaming::classToName('SomeNameCommand');
        $this->assertSame('some-name', $name);

        $name = CommandNaming::classToName('AlsoNamespace\SomeNameCommand');
        $this->assertSame('some-name', $name);
    }
}
