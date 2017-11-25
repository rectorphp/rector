<?php declare(strict_types=1);

namespace Rector\Tests\Configuration\Normalizer;

use PHPUnit\Framework\TestCase;
use Rector\Configuration\Normalizer\RectorClassNormalizer;
use Rector\Exception\Configuration\InvalidConfigurationTypeException;

final class RectorClassNormalizerTest extends TestCase
{
    /**
     * @var RectorClassNormalizer
     */
    private $rectorClassNormalizer;

    protected function setUp(): void
    {
        $this->rectorClassNormalizer = new RectorClassNormalizer();
    }

    /**
     * @dataProvider provideRectorsAndNormalizedRectors()
     *
     * @param mixed[] $rectors
     * @param mixed[] $normalizedRectors
     */
    public function test(array $rectors, array $normalizedRectors): void
    {
        $this->assertSame($normalizedRectors, $this->rectorClassNormalizer->normalize($rectors));
    }

    public function testInvalidConfig(): void
    {
        $this->expectException(InvalidConfigurationTypeException::class);
        $this->rectorClassNormalizer->normalize([
            'rector' => 'config',
        ]);
    }

    /**
     * @return mixed[][]
     */
    public function provideRectorsAndNormalizedRectors(): array
    {
        return [
            [
                ['rector'],
                [
                    'rector' => [],
                ],
            ],
            // commented configurtaion
            [
                ['rector' => null],
                [
                    'rector' => [],
                ],
            ],
            // with configuration
            [
                ['rector' => ['yes']],
                [
                    'rector' => ['yes'],
                ],
            ],
        ];
    }
}
