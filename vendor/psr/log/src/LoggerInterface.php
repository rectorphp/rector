<?php

namespace RectorPrefix202410\Psr\Log;

/**
 * Describes a logger instance.
 *
 * The message MUST be a string or object implementing __toString().
 *
 * The message MAY contain placeholders in the form: {foo} where foo
 * will be replaced by the context data in key "foo".
 *
 * The context array can contain arbitrary data. The only assumption that
 * can be made by implementors is that if an Exception instance is given
 * to produce a stack trace, it MUST be in a key named "exception".
 *
 * See https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-3-logger-interface.md
 * for the full interface specification.
 */
interface LoggerInterface
{
    /**
     * System is unusable.
     *
     * @param mixed[] $context
     * @param string|\Stringable $message
     */
    public function emergency($message, array $context = []) : void;
    /**
     * Action must be taken immediately.
     *
     * Example: Entire website down, database unavailable, etc. This should
     * trigger the SMS alerts and wake you up.
     *
     * @param mixed[] $context
     * @param string|\Stringable $message
     */
    public function alert($message, array $context = []) : void;
    /**
     * Critical conditions.
     *
     * Example: Application component unavailable, unexpected exception.
     *
     * @param mixed[] $context
     * @param string|\Stringable $message
     */
    public function critical($message, array $context = []) : void;
    /**
     * Runtime errors that do not require immediate action but should typically
     * be logged and monitored.
     *
     * @param mixed[] $context
     * @param string|\Stringable $message
     */
    public function error($message, array $context = []) : void;
    /**
     * Exceptional occurrences that are not errors.
     *
     * Example: Use of deprecated APIs, poor use of an API, undesirable things
     * that are not necessarily wrong.
     *
     * @param mixed[] $context
     * @param string|\Stringable $message
     */
    public function warning($message, array $context = []) : void;
    /**
     * Normal but significant events.
     *
     * @param mixed[] $context
     * @param string|\Stringable $message
     */
    public function notice($message, array $context = []) : void;
    /**
     * Interesting events.
     *
     * Example: User logs in, SQL logs.
     *
     * @param mixed[] $context
     * @param string|\Stringable $message
     */
    public function info($message, array $context = []) : void;
    /**
     * Detailed debug information.
     *
     * @param mixed[] $context
     * @param string|\Stringable $message
     */
    public function debug($message, array $context = []) : void;
    /**
     * Logs with an arbitrary level.
     *
     * @param mixed $level
     * @param mixed[] $context
     *
     * @throws \Psr\Log\InvalidArgumentException
     * @param string|\Stringable $message
     */
    public function log($level, $message, array $context = []) : void;
}
