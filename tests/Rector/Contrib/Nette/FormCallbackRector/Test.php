<?php declare(strict_types=1);

namespace Rector\Tests\Rector\Contrib\Nette\FormCallbackRector;

use Rector\Contract\Rector\RectorInterface;
use Rector\Rector\Contrib\Nette\FormCallbackRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class Test extends AbstractRectorTestCase
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
        return [FormCallbackRector::class];
    }
}
