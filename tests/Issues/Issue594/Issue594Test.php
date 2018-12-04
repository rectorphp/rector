<?php declare(strict_types=1);

namespace Rector\Tests\Issues\Issue594;

use Rector\Symfony\Rector\HttpKernel\GetRequestRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class Issue594Test extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([__DIR__ . '/Wrong/wrong594.php']);
    }

    public function getRectorClass(): string
    {
        return GetRequestRector::class;
    }
}
