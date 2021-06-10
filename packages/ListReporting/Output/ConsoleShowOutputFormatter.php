<?php

declare(strict_types=1);

namespace Rector\ListReporting\Output;

use Rector\Core\Contract\Console\OutputStyleInterface;
use Rector\ListReporting\Contract\Output\ShowOutputFormatterInterface;

final class ConsoleShowOutputFormatter implements ShowOutputFormatterInterface
{
    /**
     * @var string
     */
    public const NAME = 'console';

    public function __construct(
        private OutputStyleInterface $outputStyle
    ) {
    }

    public function list(array $rectors): void
    {
        $rectorCount = count($rectors);
        $this->outputStyle->title('Loaded Rector rules');

        foreach ($rectors as $rector) {
            $this->outputStyle->writeln(' * ' . $rector::class);
        }

        $message = sprintf('%d loaded Rectors', $rectorCount);
        $this->outputStyle->success($message);
    }

    public function getName(): string
    {
        return self::NAME;
    }
}
