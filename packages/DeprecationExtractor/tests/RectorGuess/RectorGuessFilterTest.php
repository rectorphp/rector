<?php declare(strict_types=1);

namespace Rector\DeprecationExtractor\Tests\RectorGuess;

use PhpParser\Node\Stmt\Nop;
use Rector\DeprecationExtractor\RectorGuess\RectorGuessFactory;
use Rector\DeprecationExtractor\RectorGuess\RectorGuessFilter;
use Rector\Tests\AbstractContainerAwareTestCase;

final class RectorGuessFilterTest extends AbstractContainerAwareTestCase
{
    /**
     * @var RectorGuessFactory
     */
    private $rectorGuessFactory;

    /**
     * @var RectorGuessFilter
     */
    private $rectorGuessFilter;

    protected function setUp(): void
    {
        $this->rectorGuessFactory = $this->container->get(RectorGuessFactory::class);
        $this->rectorGuessFilter = $this->container->get(RectorGuessFilter::class);
    }

    public function test(): void
    {
        $rectorGuesses = [];

        $rectorGuesses[] = $this->rectorGuessFactory->createClassReplacer('message', new Nop());
        $rectorGuesses[] = $this->rectorGuessFactory->createUnsupported('message', new Nop());

        $this->assertCount(2, $rectorGuesses);

        $filteredRectorGuesses = $this->rectorGuessFilter->filterRectorGuessesToShow($rectorGuesses);

        $this->assertCount(1, $filteredRectorGuesses);
    }
}
