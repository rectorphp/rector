<?php declare(strict_types=1);

namespace Rector\Tests\Rector\Argument\ArgumentRemoverRector;

use Rector\Rector\Argument\ArgumentRemoverRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Tests\Rector\Argument\ArgumentRemoverRector\Source\Persister;
use Symfony\Component\Yaml\Yaml;

final class ArgumentRemoverRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([
            __DIR__ . '/Fixture/fixture.php.inc',
            __DIR__ . '/Fixture/fixture2.php.inc',
            __DIR__ . '/Fixture/remove_in_middle.php.inc',
        ]);
    }

    protected function getRectorClass(): string
    {
        return ArgumentRemoverRector::class;
    }

    /**
     * @return mixed[]
     */
    protected function getRectorConfiguration(): array
    {
        return [
            Persister::class => [
                'getSelectJoinColumnSQL' => [
                    4 => null
                ]
            ],
            Yaml::class => [
                'parse' => [
                    1 => [
                        'Symfony\Component\Yaml\Yaml::PARSE_KEYS_AS_STRINGS',
                        'hey',
                        55,
                        5.5,
                    ]
                ]
            ],
        ];
    }
}
