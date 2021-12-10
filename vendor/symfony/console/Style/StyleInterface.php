<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RectorPrefix20211210\Symfony\Component\Console\Style;

/**
 * Output style helpers.
 *
 * @author Kevin Bond <kevinbond@gmail.com>
 */
interface StyleInterface
{
    /**
     * Formats a command title.
     * @param string $message
     */
    public function title($message);
    /**
     * Formats a section title.
     * @param string $message
     */
    public function section($message);
    /**
     * Formats a list.
     * @param mixed[] $elements
     */
    public function listing($elements);
    /**
     * Formats informational text.
     * @param mixed[]|string $message
     */
    public function text($message);
    /**
     * Formats a success result bar.
     * @param mixed[]|string $message
     */
    public function success($message);
    /**
     * Formats an error result bar.
     * @param mixed[]|string $message
     */
    public function error($message);
    /**
     * Formats an warning result bar.
     * @param mixed[]|string $message
     */
    public function warning($message);
    /**
     * Formats a note admonition.
     * @param mixed[]|string $message
     */
    public function note($message);
    /**
     * Formats a caution admonition.
     * @param mixed[]|string $message
     */
    public function caution($message);
    /**
     * Formats a table.
     * @param mixed[] $headers
     * @param mixed[] $rows
     */
    public function table($headers, $rows);
    /**
     * Asks a question.
     * @return mixed
     * @param string $question
     * @param string|null $default
     * @param callable|null $validator
     */
    public function ask($question, $default = null, $validator = null);
    /**
     * Asks a question with the user input hidden.
     * @return mixed
     * @param string $question
     * @param callable|null $validator
     */
    public function askHidden($question, $validator = null);
    /**
     * Asks for confirmation.
     * @param string $question
     * @param bool $default
     */
    public function confirm($question, $default = \true) : bool;
    /**
     * Asks a choice question.
     * @param mixed $default
     * @return mixed
     * @param string $question
     * @param mixed[] $choices
     */
    public function choice($question, $choices, $default = null);
    /**
     * Add newline(s).
     * @param int $count
     */
    public function newLine($count = 1);
    /**
     * Starts the progress output.
     * @param int $max
     */
    public function progressStart($max = 0);
    /**
     * Advances the progress output X steps.
     * @param int $step
     */
    public function progressAdvance($step = 1);
    /**
     * Finishes the progress output.
     */
    public function progressFinish();
}
