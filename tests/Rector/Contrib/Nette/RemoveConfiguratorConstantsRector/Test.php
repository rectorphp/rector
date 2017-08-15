<?php declare(strict_types=1);

namespace Rector\Tests\Rector\Contrib\Nette\RemoveConfiguratorConstantsRector;

use Rector\Rector\Contrib\Nette\RemoveConfiguratorConstantsRector;
use Rector\Testing\PHPUnit\AbstractReconstructorTestCase;

final class Test extends AbstractReconstructorTestCase
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
        return [RemoveConfiguratorConstantsRector::class];
    }
}
