<?php declare(strict_types=1);

namespace Rector\DeprecationExtractor\Tests\RectorGuess;

use PhpParser\Node\Stmt\Nop;
use PHPUnit\Framework\TestCase;
use Rector\DeprecationExtractor\RectorGuess\RectorGuess;
use Rector\DeprecationExtractor\RectorGuess\RectorGuessFactory;
use Rector\Rector\Dynamic\ClassReplacerRector;
use Rector\Rector\Dynamic\MethodArgumentChangerRector;
use Rector\Rector\Dynamic\MethodNameReplacerRector;

final class RectorGuessFactoryTest extends TestCase
{
    /**
     * @var RectorGuessFactory
     */
    private $rectorGuessFactory;

    protected function setUp(): void
    {
        $this->rectorGuessFactory = new RectorGuessFactory;
    }

    /**
     * @dataProvider provideMethodTypeAndIsUseful()
     */
    public function test(string $method, string $expectedType, bool $isUseful): void
    {
        /** @var RectorGuess $rectorGuess */
        $rectorGuess = $this->rectorGuessFactory->$method('message', new Nop);

        $this->assertSame($expectedType, $rectorGuess->getGuessedRectorClass());
        $this->assertSame($isUseful, $rectorGuess->isUseful());
    }

    /**
     * @return mixed[][]
     */
    public function provideMethodTypeAndIsUseful(): array
    {
        return [
            ['createClassReplacer', ClassReplacerRector::class, true],
            ['createMethodNameReplacerGuess', MethodNameReplacerRector::class, true],
            ['createNewArgument', MethodArgumentChangerRector::class, true],
            ['createRemoval', RectorGuess::TYPE_REMOVAL, false],
            ['createUnsupported', RectorGuess::TYPE_UNSUPPORTED, false],
        ];
    }
}
