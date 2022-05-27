<?php

declare(strict_types=1);

namespace Rector\Core\Console\Command;

use Clue\React\NDJson\Decoder;
use Clue\React\NDJson\Encoder;
use React\EventLoop\StreamSelectLoop;
use React\Socket\ConnectionInterface;
use React\Socket\TcpConnector;
use Rector\Core\Util\MemoryLimiter;
use Rector\Parallel\WorkerRunner;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symplify\EasyParallel\Enum\Action;
use Symplify\EasyParallel\Enum\ReactCommand;

/**
 * Inspired at: https://github.com/phpstan/phpstan-src/commit/9124c66dcc55a222e21b1717ba5f60771f7dda92
 * https://github.com/phpstan/phpstan-src/blob/c471c7b050e0929daf432288770de673b394a983/src/Command/WorkerCommand.php
 *
 * ↓↓↓
 * https://github.com/phpstan/phpstan-src/commit/b84acd2e3eadf66189a64fdbc6dd18ff76323f67#diff-7f625777f1ce5384046df08abffd6c911cfbb1cfc8fcb2bdeaf78f337689e3e2
 */
final class WorkerCommand extends AbstractProcessCommand
{
    public function __construct(
        private readonly WorkerRunner $workerRunner,
        private readonly MemoryLimiter $memoryLimiter,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName('worker');
        $this->setDescription('(Internal) Support for parallel process');
        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $configuration = $this->configurationFactory->createFromInput($input);
        $this->memoryLimiter->adjust($configuration);

        $streamSelectLoop = new StreamSelectLoop();
        $parallelIdentifier = $configuration->getParallelIdentifier();

        $tcpConnector = new TcpConnector($streamSelectLoop);

        $promise = $tcpConnector->connect('127.0.0.1:' . $configuration->getParallelPort());
        $promise->then(function (ConnectionInterface $connection) use ($parallelIdentifier, $configuration): void {
            $inDecoder = new Decoder($connection, true, 512, JSON_INVALID_UTF8_IGNORE);
            $outEncoder = new Encoder($connection, JSON_INVALID_UTF8_IGNORE);

            // handshake?
            $outEncoder->write([
                ReactCommand::ACTION => Action::HELLO,
                ReactCommand::IDENTIFIER => $parallelIdentifier,
            ]);

            $this->workerRunner->run($outEncoder, $inDecoder, $configuration);
        });

        $streamSelectLoop->run();

        return self::SUCCESS;
    }
}
