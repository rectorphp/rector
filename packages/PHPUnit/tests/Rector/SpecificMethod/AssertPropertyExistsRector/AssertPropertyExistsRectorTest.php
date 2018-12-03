<?php declare(strict_types=1);

namespace Rector\PHPUnit\Tests\Rector\SpecificMethod\AssertPropertyExistsRector;

use Rector\PHPUnit\Rector\SpecificMethod\AssertPropertyExistsRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class AssertPropertyExistsRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles(
            [[__DIR__ . '/Wrong/wrong.php.inc', __DIR__ . '/Correct/correct.php.inc'], [
                __DIR__ . '/Wrong/wrong2.php.inc',
                __DIR__ . '/Correct/correct2.php.inc',
            ]]
        );
    }

    public function getRectorClass(): string
    {
        return AssertPropertyExistsRector::class;
    }
}
