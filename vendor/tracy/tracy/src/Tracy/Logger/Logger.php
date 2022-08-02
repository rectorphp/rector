<?php

/**
 * This file is part of the Tracy (https://tracy.nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */
declare (strict_types=1);
namespace RectorPrefix202208\Tracy;

/**
 * Logger.
 */
class Logger implements ILogger
{
    /** @var string|null name of the directory where errors should be logged */
    public $directory;
    /** @var string|array|null email or emails to which send error notifications */
    public $email;
    /** @var string|null sender of email notifications */
    public $fromEmail;
    /** @var mixed interval for sending email is 2 days */
    public $emailSnooze = '2 days';
    /** @var callable handler for sending emails */
    public $mailer;
    /** @var BlueScreen|null */
    private $blueScreen;
    /**
     * @param  string|array|null  $email
     */
    public function __construct(?string $directory, $email = null, ?BlueScreen $blueScreen = null)
    {
        $this->directory = $directory;
        $this->email = $email;
        $this->blueScreen = $blueScreen;
        $this->mailer = [$this, 'defaultMailer'];
    }
    /**
     * Logs message or exception to file and sends email notification.
     * @param  mixed  $message
     * @param  string  $level  one of constant ILogger::INFO, WARNING, ERROR (sends email), EXCEPTION (sends email), CRITICAL (sends email)
     * @return string|null logged error filename
     */
    public function log($message, $level = self::INFO)
    {
        if (!$this->directory) {
            throw new \LogicException('Logging directory is not specified.');
        } elseif (!\is_dir($this->directory)) {
            throw new \RuntimeException("Logging directory '{$this->directory}' is not found or is not directory.");
        }
        $exceptionFile = $message instanceof \Throwable ? $this->getExceptionFile($message, $level) : null;
        $line = static::formatLogLine($message, $exceptionFile);
        $file = $this->directory . '/' . \strtolower($level ?: self::INFO) . '.log';
        if (!@\file_put_contents($file, $line . \PHP_EOL, \FILE_APPEND | \LOCK_EX)) {
            // @ is escalated to exception
            throw new \RuntimeException("Unable to write to log file '{$file}'. Is directory writable?");
        }
        if ($exceptionFile) {
            $this->logException($message, $exceptionFile);
        }
        if (\in_array($level, [self::ERROR, self::EXCEPTION, self::CRITICAL], \true)) {
            $this->sendEmail($message);
        }
        return $exceptionFile;
    }
    /**
     * @param  mixed  $message
     */
    public static function formatMessage($message) : string
    {
        if ($message instanceof \Throwable) {
            foreach (Helpers::getExceptionChain($message) as $exception) {
                $tmp[] = ($exception instanceof \ErrorException ? Helpers::errorTypeToString($exception->getSeverity()) . ': ' . $exception->getMessage() : Helpers::getClass($exception) . ': ' . $exception->getMessage() . ($exception->getCode() ? ' #' . $exception->getCode() : '')) . ' in ' . $exception->getFile() . ':' . $exception->getLine();
            }
            $message = \implode("\ncaused by ", $tmp);
        } elseif (!\is_string($message)) {
            $message = Dumper::toText($message);
        }
        return \trim($message);
    }
    /**
     * @param  mixed  $message
     */
    public static function formatLogLine($message, ?string $exceptionFile = null) : string
    {
        return \implode(' ', [\date('[Y-m-d H-i-s]'), \preg_replace('#\\s*\\r?\\n\\s*#', ' ', static::formatMessage($message)), ' @  ' . Helpers::getSource(), $exceptionFile ? ' @@  ' . \basename($exceptionFile) : null]);
    }
    public function getExceptionFile(\Throwable $exception, string $level = self::EXCEPTION) : string
    {
        foreach (Helpers::getExceptionChain($exception) as $exception) {
            $data[] = [\get_class($exception), $exception->getMessage(), $exception->getCode(), $exception->getFile(), $exception->getLine(), \array_map(function (array $item) : array {
                unset($item['args']);
                return $item;
            }, $exception->getTrace())];
        }
        $hash = \substr(\md5(\serialize($data)), 0, 10);
        $dir = \strtr($this->directory . '/', '\\/', \DIRECTORY_SEPARATOR . \DIRECTORY_SEPARATOR);
        foreach (new \DirectoryIterator($this->directory) as $file) {
            if (\strpos($file->getBasename(), $hash)) {
                return $dir . $file;
            }
        }
        return $dir . $level . '--' . \date('Y-m-d--H-i') . "--{$hash}.html";
    }
    /**
     * Logs exception to the file if file doesn't exist.
     * @return string logged error filename
     */
    protected function logException(\Throwable $exception, ?string $file = null) : string
    {
        $file = $file ?: $this->getExceptionFile($exception);
        $bs = $this->blueScreen ?: new BlueScreen();
        $bs->renderToFile($exception, $file);
        return $file;
    }
    /**
     * @param  mixed  $message
     */
    protected function sendEmail($message) : void
    {
        $snooze = \is_numeric($this->emailSnooze) ? $this->emailSnooze : \strtotime($this->emailSnooze) - \time();
        if ($this->email && $this->mailer && @\filemtime($this->directory . '/email-sent') + $snooze < \time() && @\file_put_contents($this->directory . '/email-sent', 'sent')) {
            ($this->mailer)($message, \implode(', ', (array) $this->email));
        }
    }
    /**
     * Default mailer.
     * @param  mixed  $message
     * @internal
     */
    public function defaultMailer($message, string $email) : void
    {
        $host = \preg_replace('#[^\\w.-]+#', '', $_SERVER['SERVER_NAME'] ?? \php_uname('n'));
        $parts = \str_replace(["\r\n", "\n"], ["\n", \PHP_EOL], ['headers' => \implode("\n", ['From: ' . ($this->fromEmail ?: "noreply@{$host}"), 'X-Mailer: Tracy', 'Content-Type: text/plain; charset=UTF-8', 'Content-Transfer-Encoding: 8bit']) . "\n", 'subject' => "PHP: An error occurred on the server {$host}", 'body' => static::formatMessage($message) . "\n\nsource: " . Helpers::getSource()]);
        \mail($email, $parts['subject'], $parts['body'], $parts['headers']);
    }
}
