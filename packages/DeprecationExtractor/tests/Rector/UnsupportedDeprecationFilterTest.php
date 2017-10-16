<?php declare(strict_types=1);

namespace Rector\DeprecationExtractor\Tests\Rector;

use PhpParser\Node\Stmt\Nop;
use Rector\DeprecationExtractor\Deprecation\Deprecation;
use Rector\DeprecationExtractor\Rector\UnsupportedDeprecationFilter;
use Rector\Tests\AbstractContainerAwareTestCase;

final class UnsupportedDeprecationFilterTest extends AbstractContainerAwareTestCase
{
    /**
     * @var UnsupportedDeprecationFilter
     */
    private $unsupportedDeprecationFilter;

    protected function setUp(): void
    {
        $this->unsupportedDeprecationFilter = $this->container->get(UnsupportedDeprecationFilter::class);
    }

    /**
     * @dataProvider provideDeprecationMessages()
     */
    public function test(string $message, bool $isMatched): void
    {
        $deprecation = Deprecation::createFromMessageAndNode($message, new Nop);

        $this->assertSame($isMatched, $this->unsupportedDeprecationFilter->matches($deprecation));
    }

    /**
     * @return string[][]|bool[][]
     */
    public function provideDeprecationMessages(): array
    {
        return [
            ['This service is private', true],
            ['Service names that start with an underscore are deprecated', true],
            ['someMessage', false],
        ];
    }
}
