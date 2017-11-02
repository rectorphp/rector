<?php declare(strict_types=1);

namespace Rector\DeprecationExtractor\Tests\Rector;

use PhpParser\Node\Stmt\Nop;
use Rector\DeprecationExtractor\Deprecation\Deprecation;
use Rector\DeprecationExtractor\Rector\RectorGuesser;
use Rector\DeprecationExtractor\RectorGuess\RectorGuess;
use Rector\Rector\Dynamic\ArgumentReplacerRector;
use Rector\Rector\Dynamic\ClassReplacerRector;
use Rector\Rector\Dynamic\MethodNameReplacerRector;
use Rector\Tests\AbstractContainerAwareTestCase;

final class RectorGuesserTest extends AbstractContainerAwareTestCase
{
    /**
     * @var RectorGuesser
     */
    private $rectorGuesser;

    protected function setUp(): void
    {
        $this->rectorGuesser = $this->container->get(RectorGuesser::class);
    }

    /**
     * @dataProvider provideDeprecationMessagesAndExpected()
     */
    public function test(string $message, string $expectedType): void
    {
        $deprecations = [
            Deprecation::createFromMessageAndNode($message, new Nop),
        ];

        $rectorGuesses = $this->rectorGuesser->guessForDeprecations($deprecations);
        $this->assertCount(1, $rectorGuesses);

        $rectorGuess = $rectorGuesses[0];
        $this->assertInstanceOf(RectorGuess::class, $rectorGuess);
        $this->assertSame($expectedType, $rectorGuess->getGuessedRectorClass());
    }

    /**
     * @return mixed[][]
     */
    public function provideDeprecationMessagesAndExpected(): array
    {
        return [
            ['This class is deprecated, use AnotherClass instead', ClassReplacerRector::class],
            ['This method is deprecated, use method() instead', MethodNameReplacerRector::class],
            ['It will be made mandatory in Symfony 4.0', ArgumentReplacerRector::class],
            ['removed', RectorGuess::TYPE_REMOVAL],
            ['service is private', RectorGuess::TYPE_UNSUPPORTED],
        ];
    }
}
