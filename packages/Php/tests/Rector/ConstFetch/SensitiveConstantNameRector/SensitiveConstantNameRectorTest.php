<?php declare(strict_types=1);

namespace Rector\Php\Tests\Rector\ConstFetch\SensitiveConstantNameRector;

use Rector\Php\Rector\ConstFetch\SensitiveConstantNameRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class SensitiveConstantNameRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([__DIR__ . '/Wrong/wrong.php.inc']);
    }

    public function getRectorClass(): string
    {
        return SensitiveConstantNameRector::class;
    }
}
