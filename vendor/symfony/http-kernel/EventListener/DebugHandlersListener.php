<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RectorPrefix20211020\Symfony\Component\HttpKernel\EventListener;

use RectorPrefix20211020\Psr\Log\LoggerInterface;
use RectorPrefix20211020\Symfony\Component\Console\ConsoleEvents;
use RectorPrefix20211020\Symfony\Component\Console\Event\ConsoleEvent;
use RectorPrefix20211020\Symfony\Component\Console\Output\ConsoleOutputInterface;
use RectorPrefix20211020\Symfony\Component\ErrorHandler\ErrorHandler;
use RectorPrefix20211020\Symfony\Component\EventDispatcher\EventSubscriberInterface;
use RectorPrefix20211020\Symfony\Component\HttpKernel\Debug\FileLinkFormatter;
use RectorPrefix20211020\Symfony\Component\HttpKernel\Event\KernelEvent;
use RectorPrefix20211020\Symfony\Component\HttpKernel\KernelEvents;
/**
 * Configures errors and exceptions handlers.
 *
 * @author Nicolas Grekas <p@tchwork.com>
 *
 * @final
 *
 * @internal since Symfony 5.3
 */
class DebugHandlersListener implements \RectorPrefix20211020\Symfony\Component\EventDispatcher\EventSubscriberInterface
{
    private $earlyHandler;
    private $exceptionHandler;
    private $logger;
    private $deprecationLogger;
    private $levels;
    private $throwAt;
    private $scream;
    private $fileLinkFormat;
    private $scope;
    private $firstCall = \true;
    private $hasTerminatedWithException;
    /**
     * @param callable|null                 $exceptionHandler A handler that must support \Throwable instances that will be called on Exception
     * @param array|int                     $levels           An array map of E_* to LogLevel::* or an integer bit field of E_* constants
     * @param int|null                      $throwAt          Thrown errors in a bit field of E_* constants, or null to keep the current value
     * @param bool                          $scream           Enables/disables screaming mode, where even silenced errors are logged
     * @param string|FileLinkFormatter|null $fileLinkFormat   The format for links to source files
     * @param bool                          $scope            Enables/disables scoping mode
     */
    public function __construct(callable $exceptionHandler = null, \RectorPrefix20211020\Psr\Log\LoggerInterface $logger = null, $levels = \E_ALL, ?int $throwAt = \E_ALL, bool $scream = \true, $fileLinkFormat = null, bool $scope = \true, \RectorPrefix20211020\Psr\Log\LoggerInterface $deprecationLogger = null)
    {
        $handler = \set_exception_handler('var_dump');
        $this->earlyHandler = \is_array($handler) ? $handler[0] : null;
        \restore_exception_handler();
        $this->exceptionHandler = $exceptionHandler;
        $this->logger = $logger;
        $this->levels = $levels ?? \E_ALL;
        $this->throwAt = \is_int($throwAt) ? $throwAt : (null === $throwAt ? null : ($throwAt ? \E_ALL : null));
        $this->scream = $scream;
        $this->fileLinkFormat = $fileLinkFormat;
        $this->scope = $scope;
        $this->deprecationLogger = $deprecationLogger;
    }
    /**
     * Configures the error handler.
     * @param object $event
     */
    public function configure($event = null)
    {
        if ($event instanceof \RectorPrefix20211020\Symfony\Component\Console\Event\ConsoleEvent && !\in_array(\PHP_SAPI, ['cli', 'phpdbg'], \true)) {
            return;
        }
        if (!$event instanceof \RectorPrefix20211020\Symfony\Component\HttpKernel\Event\KernelEvent ? !$this->firstCall : !$event->isMainRequest()) {
            return;
        }
        $this->firstCall = $this->hasTerminatedWithException = \false;
        $handler = \set_exception_handler('var_dump');
        $handler = \is_array($handler) ? $handler[0] : null;
        \restore_exception_handler();
        if (!$handler instanceof \RectorPrefix20211020\Symfony\Component\ErrorHandler\ErrorHandler) {
            $handler = $this->earlyHandler;
        }
        if ($handler instanceof \RectorPrefix20211020\Symfony\Component\ErrorHandler\ErrorHandler) {
            if ($this->logger || $this->deprecationLogger) {
                $this->setDefaultLoggers($handler);
                if (\is_array($this->levels)) {
                    $levels = 0;
                    foreach ($this->levels as $type => $log) {
                        $levels |= $type;
                    }
                } else {
                    $levels = $this->levels;
                }
                if ($this->scream) {
                    $handler->screamAt($levels);
                }
                if ($this->scope) {
                    $handler->scopeAt($levels & ~\E_USER_DEPRECATED & ~\E_DEPRECATED);
                } else {
                    $handler->scopeAt(0, \true);
                }
                $this->logger = $this->deprecationLogger = $this->levels = null;
            }
            if (null !== $this->throwAt) {
                $handler->throwAt($this->throwAt, \true);
            }
        }
        if (!$this->exceptionHandler) {
            if ($event instanceof \RectorPrefix20211020\Symfony\Component\HttpKernel\Event\KernelEvent) {
                if (\method_exists($kernel = $event->getKernel(), 'terminateWithException')) {
                    $request = $event->getRequest();
                    $hasRun =& $this->hasTerminatedWithException;
                    $this->exceptionHandler = static function (\Throwable $e) use($kernel, $request, &$hasRun) {
                        if ($hasRun) {
                            throw $e;
                        }
                        $hasRun = \true;
                        $kernel->terminateWithException($e, $request);
                    };
                }
            } elseif ($event instanceof \RectorPrefix20211020\Symfony\Component\Console\Event\ConsoleEvent && ($app = $event->getCommand()->getApplication())) {
                $output = $event->getOutput();
                if ($output instanceof \RectorPrefix20211020\Symfony\Component\Console\Output\ConsoleOutputInterface) {
                    $output = $output->getErrorOutput();
                }
                $this->exceptionHandler = static function (\Throwable $e) use($app, $output) {
                    $app->renderThrowable($e, $output);
                };
            }
        }
        if ($this->exceptionHandler) {
            if ($handler instanceof \RectorPrefix20211020\Symfony\Component\ErrorHandler\ErrorHandler) {
                $handler->setExceptionHandler($this->exceptionHandler);
            }
            $this->exceptionHandler = null;
        }
    }
    private function setDefaultLoggers(\RectorPrefix20211020\Symfony\Component\ErrorHandler\ErrorHandler $handler) : void
    {
        if (\is_array($this->levels)) {
            $levelsDeprecatedOnly = [];
            $levelsWithoutDeprecated = [];
            foreach ($this->levels as $type => $log) {
                if (\E_DEPRECATED == $type || \E_USER_DEPRECATED == $type) {
                    $levelsDeprecatedOnly[$type] = $log;
                } else {
                    $levelsWithoutDeprecated[$type] = $log;
                }
            }
        } else {
            $levelsDeprecatedOnly = $this->levels & (\E_DEPRECATED | \E_USER_DEPRECATED);
            $levelsWithoutDeprecated = $this->levels & ~\E_DEPRECATED & ~\E_USER_DEPRECATED;
        }
        $defaultLoggerLevels = $this->levels;
        if ($this->deprecationLogger && $levelsDeprecatedOnly) {
            $handler->setDefaultLogger($this->deprecationLogger, $levelsDeprecatedOnly);
            $defaultLoggerLevels = $levelsWithoutDeprecated;
        }
        if ($this->logger && $defaultLoggerLevels) {
            $handler->setDefaultLogger($this->logger, $defaultLoggerLevels);
        }
    }
    public static function getSubscribedEvents() : array
    {
        $events = [\RectorPrefix20211020\Symfony\Component\HttpKernel\KernelEvents::REQUEST => ['configure', 2048]];
        if (\defined('Symfony\\Component\\Console\\ConsoleEvents::COMMAND')) {
            $events[\RectorPrefix20211020\Symfony\Component\Console\ConsoleEvents::COMMAND] = ['configure', 2048];
        }
        return $events;
    }
}
