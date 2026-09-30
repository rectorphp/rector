<?php

declare (strict_types=1);
namespace Rector\Parallel\ValueObject;

use RectorPrefix202609\Clue\React\NDJson\Decoder;
use RectorPrefix202609\Clue\React\NDJson\Encoder;
use Exception;
use RectorPrefix202609\React\ChildProcess\Process;
use RectorPrefix202609\React\EventLoop\LoopInterface;
use RectorPrefix202609\React\EventLoop\TimerInterface;
use Rector\Parallel\Enum\Action;
use Rector\Parallel\Enum\Content;
use Rector\Parallel\Enum\ReactCommand;
use Rector\Parallel\Enum\ReactEvent;
use Rector\Parallel\Exception\ParallelShouldNotHappenException;
use Throwable;
/**
 * Inspired at @see https://raw.githubusercontent.com/phpstan/phpstan-src/master/src/Parallel/Process.php
 * @see \Rector\Tests\Parallel\ValueObject\ParallelProcessTest
 */
final class ParallelProcess
{
    /**
     * @readonly
     */
    private string $command;
    /**
     * @readonly
     */
    private LoopInterface $loop;
    /**
     * @readonly
     */
    private int $timetoutInSeconds;
    private Process $process;
    private Encoder $encoder;
    /**
     * @var resource|null
     */
    private $stdErr;
    /**
     * @var callable(mixed[]) : void
     */
    private $onData;
    /**
     * @var callable(Throwable): void
     */
    private $onError;
    private ?TimerInterface $timer = null;
    public function __construct(string $command, LoopInterface $loop, int $timetoutInSeconds)
    {
        $this->command = $command;
        $this->loop = $loop;
        $this->timetoutInSeconds = $timetoutInSeconds;
    }
    /**
     * @param callable(mixed[] $onData) : void $onData
     * @param callable(Throwable $onError) : void $onError
     * @param callable(?int $onExit, string $output) : void $onExit
     */
    public function start(callable $onData, callable $onError, callable $onExit): void
    {
        $tmp = tmpfile();
        if ($tmp === \false) {
            throw new ParallelShouldNotHappenException('Failed creating temp file.');
        }
        $this->stdErr = $tmp;
        // on Unix, the command runs in a wrapping shell; exec replaces the shell with the worker,
        // so terminating the process stops the worker itself, not only the shell
        $command = \DIRECTORY_SEPARATOR === '\\' ? $this->command : 'exec ' . $this->command;
        $this->process = new Process($command, null, null, [2 => $this->stdErr]);
        $this->process->start($this->loop);
        $this->onData = $onData;
        $this->onError = $onError;
        $this->process->on(ReactEvent::EXIT, function ($exitCode) use ($onExit): void {
            $stdErr = $this->stdErr;
            if ($stdErr === null) {
                throw new ParallelShouldNotHappenException();
            }
            $this->cancelTimer();
            rewind($stdErr);
            /** @var string $streamContents */
            $streamContents = stream_get_contents($stdErr);
            $onExit($exitCode, $streamContents);
            fclose($stdErr);
        });
    }
    /**
     * @param mixed[] $data
     */
    public function request(array $data): void
    {
        $this->cancelTimer();
        $this->encoder->write($data);
        $this->timer = $this->loop->addTimer($this->timetoutInSeconds, function (): void {
            // a worker that does not answer in time cannot be asked to stop either
            $this->process->terminate();
            $onError = $this->onError;
            $errorMessage = sprintf('Child process timed out after %d seconds', $this->timetoutInSeconds);
            $onError(new Exception($errorMessage));
        });
    }
    public function quit(): void
    {
        if (!$this->process->isRunning()) {
            return;
        }
        foreach ($this->process->pipes as $pipe) {
            $pipe->close();
        }
        // the process can be quit before its connection is bound, e.g. on quitAll() after an error;
        // such a worker cannot be asked to stop
        if (!isset($this->encoder)) {
            $this->process->terminate();
            return;
        }
        // a busy worker keeps its timeout, so it is still terminated when it never finishes its job
        $this->encoder->end();
    }
    public function bindConnection(Decoder $decoder, Encoder $encoder): void
    {
        $decoder->on(ReactEvent::DATA, function (array $json): void {
            $this->cancelTimer();
            if ($json[ReactCommand::ACTION] !== Action::RESULT) {
                return;
            }
            $onData = $this->onData;
            $onData($json[Content::RESULT]);
        });
        $this->encoder = $encoder;
        $decoder->on(ReactEvent::ERROR, function (Throwable $throwable): void {
            $onError = $this->onError;
            $onError($throwable);
        });
        $encoder->on(ReactEvent::ERROR, function (Throwable $throwable): void {
            $onError = $this->onError;
            $onError($throwable);
        });
    }
    private function cancelTimer(): void
    {
        if (!$this->timer instanceof TimerInterface) {
            return;
        }
        $this->loop->cancelTimer($this->timer);
        $this->timer = null;
    }
}
