<?php declare(strict_types=1);

namespace Rector\Symfony\Tests\Rector\Controller\ActionSuffixRemoverRector;

use Rector\Symfony\Rector\Controller\ActionSuffixRemoverRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class ActionSuffixRemoverRectorTest extends AbstractRectorTestCase
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
        return ActionSuffixRemoverRector::class;
    }
}
