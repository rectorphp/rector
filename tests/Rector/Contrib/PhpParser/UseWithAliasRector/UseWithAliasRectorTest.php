<?php declare(strict_types=1);

namespace Rector\Tests\Rector\Contrib\PhpParser\UseWithAliasRector;

use Rector\Rector\Contrib\PhpParser\UseWithAliasRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class UseWithAliasRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFileMatchesExpectedContent(
            __DIR__ . '/wrong/wrong.php.inc',
            __DIR__ . '/correct/correct.php.inc'
        );
    }

    /**
     * @return string[]
     */
    protected function getRectorClasses(): array
    {
        return [UseWithAliasRector::class];
    }
}
