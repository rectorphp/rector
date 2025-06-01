<?php

declare (strict_types=1);
namespace RectorPrefix202506\Symplify\EasyParallel\ValueObject;

use RectorPrefix202506\Clue\React\NDJson\Decoder;
use RectorPrefix202506\Clue\React\NDJson\Encoder;
use Exception;
use RectorPrefix202506\React\ChildProcess\Process;
use RectorPrefix202506\React\EventLoop\LoopInterface;
use RectorPrefix202506\React\EventLoop\TimerInterface;
use RectorPrefix202506\Symplify\EasyParallel\Enum\Action;
use RectorPrefix202506\Symplify\EasyParallel\Enum\Content;
use RectorPrefix202506\Symplify\EasyParallel\Enum\ReactCommand;
use RectorPrefix202506\Symplify\EasyParallel\Enum\ReactEvent;
use RectorPrefix202506\Symplify\EasyParallel\Exception\ParallelShouldNotHappenException;
use Throwable;
/**
 * Inspired at @see https://raw.githubusercontent.com/phpstan/phpstan-src/master/src/Parallel/Process.php
 *
 * @api
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
    public function start(callable $onData, callable $onError, callable $onExit) : void
    {
        $tmp = \tmpfile();
        if ($tmp === \false) {
            throw new ParallelShouldNotHappenException('Failed creating temp file.');
        }
        $this->stdErr = $tmp;
        $this->process = new Process($this->command, null, null, [2 => $this->stdErr]);
        $this->process->start($this->loop);
        $this->onData = $onData;
        $this->onError = $onError;
        $this->process->on(ReactEvent::EXIT, function ($exitCode) use($onExit) : void {
            if ($this->stdErr === null) {
                throw new ParallelShouldNotHappenException();
            }
            $this->cancelTimer();
            \rewind($this->stdErr);
            /** @var string $streamContents */
            $streamContents = \stream_get_contents($this->stdErr);
            $onExit($exitCode, $streamContents);
            \fclose($this->stdErr);
        });
    }
    /**
     * @param mixed[] $data
     */
    public function request(array $data) : void
    {
        $this->cancelTimer();
        $this->encoder->write($data);
        $this->timer = $this->loop->addTimer($this->timetoutInSeconds, function () : void {
            $onError = $this->onError;
            $errorMessage = \sprintf('Child process timed out after %d seconds', $this->timetoutInSeconds);
            $onError(new Exception($errorMessage));
        });
    }
    public function quit() : void
    {
        $this->cancelTimer();
        if (!$this->process->isRunning()) {
            return;
        }
        foreach ($this->process->pipes as $pipe) {
            $pipe->close();
        }
        $this->encoder->end();
    }
    public function bindConnection(Decoder $decoder, Encoder $encoder) : void
    {
        $decoder->on(ReactEvent::DATA, function (array $json) : void {
            $this->cancelTimer();
            if ($json[ReactCommand::ACTION] !== Action::RESULT) {
                return;
            }
            $onData = $this->onData;
            $onData($json[Content::RESULT]);
        });
        $this->encoder = $encoder;
        $decoder->on(ReactEvent::ERROR, function (Throwable $throwable) : void {
            $onError = $this->onError;
            $onError($throwable);
        });
        $encoder->on(ReactEvent::ERROR, function (Throwable $throwable) : void {
            $onError = $this->onError;
            $onError($throwable);
        });
    }
    private function cancelTimer() : void
    {
        if (!$this->timer instanceof TimerInterface) {
            return;
        }
        $this->loop->cancelTimer($this->timer);
        $this->timer = null;
    }
}
