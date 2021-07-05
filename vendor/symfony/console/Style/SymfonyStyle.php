<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RectorPrefix20210705\Symfony\Component\Console\Style;

use RectorPrefix20210705\Symfony\Component\Console\Exception\InvalidArgumentException;
use RectorPrefix20210705\Symfony\Component\Console\Exception\RuntimeException;
use RectorPrefix20210705\Symfony\Component\Console\Formatter\OutputFormatter;
use RectorPrefix20210705\Symfony\Component\Console\Helper\Helper;
use RectorPrefix20210705\Symfony\Component\Console\Helper\ProgressBar;
use RectorPrefix20210705\Symfony\Component\Console\Helper\SymfonyQuestionHelper;
use RectorPrefix20210705\Symfony\Component\Console\Helper\Table;
use RectorPrefix20210705\Symfony\Component\Console\Helper\TableCell;
use RectorPrefix20210705\Symfony\Component\Console\Helper\TableSeparator;
use RectorPrefix20210705\Symfony\Component\Console\Input\InputInterface;
use RectorPrefix20210705\Symfony\Component\Console\Output\OutputInterface;
use RectorPrefix20210705\Symfony\Component\Console\Output\TrimmedBufferOutput;
use RectorPrefix20210705\Symfony\Component\Console\Question\ChoiceQuestion;
use RectorPrefix20210705\Symfony\Component\Console\Question\ConfirmationQuestion;
use RectorPrefix20210705\Symfony\Component\Console\Question\Question;
use RectorPrefix20210705\Symfony\Component\Console\Terminal;
/**
 * Output decorator helpers for the Symfony Style Guide.
 *
 * @author Kevin Bond <kevinbond@gmail.com>
 */
class SymfonyStyle extends \RectorPrefix20210705\Symfony\Component\Console\Style\OutputStyle
{
    public const MAX_LINE_LENGTH = 120;
    private $input;
    private $questionHelper;
    private $progressBar;
    private $lineLength;
    private $bufferedOutput;
    public function __construct(\RectorPrefix20210705\Symfony\Component\Console\Input\InputInterface $input, \RectorPrefix20210705\Symfony\Component\Console\Output\OutputInterface $output)
    {
        $this->input = $input;
        $this->bufferedOutput = new \RectorPrefix20210705\Symfony\Component\Console\Output\TrimmedBufferOutput(\DIRECTORY_SEPARATOR === '\\' ? 4 : 2, $output->getVerbosity(), \false, clone $output->getFormatter());
        // Windows cmd wraps lines as soon as the terminal width is reached, whether there are following chars or not.
        $width = (new \RectorPrefix20210705\Symfony\Component\Console\Terminal())->getWidth() ?: self::MAX_LINE_LENGTH;
        $this->lineLength = \min($width - (int) (\DIRECTORY_SEPARATOR === '\\'), self::MAX_LINE_LENGTH);
        parent::__construct($output);
    }
    /**
     * Formats a message as a block of text.
     *
     * @param string|array $messages The message to write in the block
     * @param string|null $type
     * @param string|null $style
     * @param string $prefix
     * @param bool $padding
     * @param bool $escape
     */
    public function block($messages, $type = null, $style = null, $prefix = ' ', $padding = \false, $escape = \true)
    {
        $messages = \is_array($messages) ? \array_values($messages) : [$messages];
        $this->autoPrependBlock();
        $this->writeln($this->createBlock($messages, $type, $style, $prefix, $padding, $escape));
        $this->newLine();
    }
    /**
     * {@inheritdoc}
     * @param string $message
     */
    public function title($message)
    {
        $this->autoPrependBlock();
        $this->writeln([\sprintf('<comment>%s</>', \RectorPrefix20210705\Symfony\Component\Console\Formatter\OutputFormatter::escapeTrailingBackslash($message)), \sprintf('<comment>%s</>', \str_repeat('=', \RectorPrefix20210705\Symfony\Component\Console\Helper\Helper::width(\RectorPrefix20210705\Symfony\Component\Console\Helper\Helper::removeDecoration($this->getFormatter(), $message))))]);
        $this->newLine();
    }
    /**
     * {@inheritdoc}
     * @param string $message
     */
    public function section($message)
    {
        $this->autoPrependBlock();
        $this->writeln([\sprintf('<comment>%s</>', \RectorPrefix20210705\Symfony\Component\Console\Formatter\OutputFormatter::escapeTrailingBackslash($message)), \sprintf('<comment>%s</>', \str_repeat('-', \RectorPrefix20210705\Symfony\Component\Console\Helper\Helper::width(\RectorPrefix20210705\Symfony\Component\Console\Helper\Helper::removeDecoration($this->getFormatter(), $message))))]);
        $this->newLine();
    }
    /**
     * {@inheritdoc}
     * @param mixed[] $elements
     */
    public function listing($elements)
    {
        $this->autoPrependText();
        $elements = \array_map(function ($element) {
            return \sprintf(' * %s', $element);
        }, $elements);
        $this->writeln($elements);
        $this->newLine();
    }
    /**
     * {@inheritdoc}
     */
    public function text($message)
    {
        $this->autoPrependText();
        $messages = \is_array($message) ? \array_values($message) : [$message];
        foreach ($messages as $message) {
            $this->writeln(\sprintf(' %s', $message));
        }
    }
    /**
     * Formats a command comment.
     *
     * @param string|array $message
     */
    public function comment($message)
    {
        $this->block($message, null, null, '<fg=default;bg=default> // </>', \false, \false);
    }
    /**
     * {@inheritdoc}
     */
    public function success($message)
    {
        $this->block($message, 'OK', 'fg=black;bg=green', ' ', \true);
    }
    /**
     * {@inheritdoc}
     */
    public function error($message)
    {
        $this->block($message, 'ERROR', 'fg=white;bg=red', ' ', \true);
    }
    /**
     * {@inheritdoc}
     */
    public function warning($message)
    {
        $this->block($message, 'WARNING', 'fg=black;bg=yellow', ' ', \true);
    }
    /**
     * {@inheritdoc}
     */
    public function note($message)
    {
        $this->block($message, 'NOTE', 'fg=yellow', ' ! ');
    }
    /**
     * Formats an info message.
     *
     * @param string|array $message
     */
    public function info($message)
    {
        $this->block($message, 'INFO', 'fg=green', ' ', \true);
    }
    /**
     * {@inheritdoc}
     */
    public function caution($message)
    {
        $this->block($message, 'CAUTION', 'fg=white;bg=red', ' ! ', \true);
    }
    /**
     * {@inheritdoc}
     * @param mixed[] $headers
     * @param mixed[] $rows
     */
    public function table($headers, $rows)
    {
        $style = clone \RectorPrefix20210705\Symfony\Component\Console\Helper\Table::getStyleDefinition('symfony-style-guide');
        $style->setCellHeaderFormat('<info>%s</info>');
        $table = new \RectorPrefix20210705\Symfony\Component\Console\Helper\Table($this);
        $table->setHeaders($headers);
        $table->setRows($rows);
        $table->setStyle($style);
        $table->render();
        $this->newLine();
    }
    /**
     * Formats a horizontal table.
     * @param mixed[] $headers
     * @param mixed[] $rows
     */
    public function horizontalTable($headers, $rows)
    {
        $style = clone \RectorPrefix20210705\Symfony\Component\Console\Helper\Table::getStyleDefinition('symfony-style-guide');
        $style->setCellHeaderFormat('<info>%s</info>');
        $table = new \RectorPrefix20210705\Symfony\Component\Console\Helper\Table($this);
        $table->setHeaders($headers);
        $table->setRows($rows);
        $table->setStyle($style);
        $table->setHorizontal(\true);
        $table->render();
        $this->newLine();
    }
    /**
     * Formats a list of key/value horizontally.
     *
     * Each row can be one of:
     * * 'A title'
     * * ['key' => 'value']
     * * new TableSeparator()
     *
     * @param string|array|TableSeparator ...$list
     */
    public function definitionList(...$list)
    {
        $style = clone \RectorPrefix20210705\Symfony\Component\Console\Helper\Table::getStyleDefinition('symfony-style-guide');
        $style->setCellHeaderFormat('<info>%s</info>');
        $table = new \RectorPrefix20210705\Symfony\Component\Console\Helper\Table($this);
        $headers = [];
        $row = [];
        foreach ($list as $value) {
            if ($value instanceof \RectorPrefix20210705\Symfony\Component\Console\Helper\TableSeparator) {
                $headers[] = $value;
                $row[] = $value;
                continue;
            }
            if (\is_string($value)) {
                $headers[] = new \RectorPrefix20210705\Symfony\Component\Console\Helper\TableCell($value, ['colspan' => 2]);
                $row[] = null;
                continue;
            }
            if (!\is_array($value)) {
                throw new \RectorPrefix20210705\Symfony\Component\Console\Exception\InvalidArgumentException('Value should be an array, string, or an instance of TableSeparator.');
            }
            $headers[] = \key($value);
            $row[] = \current($value);
        }
        $table->setHeaders($headers);
        $table->setRows([$row]);
        $table->setHorizontal();
        $table->setStyle($style);
        $table->render();
        $this->newLine();
    }
    /**
     * {@inheritdoc}
     * @param string $question
     * @param string|null $default
     * @param callable|null $validator
     */
    public function ask($question, $default = null, $validator = null)
    {
        $question = new \RectorPrefix20210705\Symfony\Component\Console\Question\Question($question, $default);
        $question->setValidator($validator);
        return $this->askQuestion($question);
    }
    /**
     * {@inheritdoc}
     * @param string $question
     * @param callable|null $validator
     */
    public function askHidden($question, $validator = null)
    {
        $question = new \RectorPrefix20210705\Symfony\Component\Console\Question\Question($question);
        $question->setHidden(\true);
        $question->setValidator($validator);
        return $this->askQuestion($question);
    }
    /**
     * {@inheritdoc}
     * @param string $question
     * @param bool $default
     */
    public function confirm($question, $default = \true)
    {
        return $this->askQuestion(new \RectorPrefix20210705\Symfony\Component\Console\Question\ConfirmationQuestion($question, $default));
    }
    /**
     * {@inheritdoc}
     * @param string $question
     * @param mixed[] $choices
     */
    public function choice($question, $choices, $default = null)
    {
        if (null !== $default) {
            $values = \array_flip($choices);
            $default = $values[$default] ?? $default;
        }
        return $this->askQuestion(new \RectorPrefix20210705\Symfony\Component\Console\Question\ChoiceQuestion($question, $choices, $default));
    }
    /**
     * {@inheritdoc}
     * @param int $max
     */
    public function progressStart($max = 0)
    {
        $this->progressBar = $this->createProgressBar($max);
        $this->progressBar->start();
    }
    /**
     * {@inheritdoc}
     * @param int $step
     */
    public function progressAdvance($step = 1)
    {
        $this->getProgressBar()->advance($step);
    }
    /**
     * {@inheritdoc}
     */
    public function progressFinish()
    {
        $this->getProgressBar()->finish();
        $this->newLine(2);
        $this->progressBar = null;
    }
    /**
     * {@inheritdoc}
     * @param int $max
     */
    public function createProgressBar($max = 0)
    {
        $progressBar = parent::createProgressBar($max);
        if ('\\' !== \DIRECTORY_SEPARATOR || 'Hyper' === \getenv('TERM_PROGRAM')) {
            $progressBar->setEmptyBarCharacter('░');
            // light shade character \u2591
            $progressBar->setProgressCharacter('');
            $progressBar->setBarCharacter('▓');
            // dark shade character \u2593
        }
        return $progressBar;
    }
    /**
     * @return mixed
     * @param \Symfony\Component\Console\Question\Question $question
     */
    public function askQuestion($question)
    {
        if ($this->input->isInteractive()) {
            $this->autoPrependBlock();
        }
        if (!$this->questionHelper) {
            $this->questionHelper = new \RectorPrefix20210705\Symfony\Component\Console\Helper\SymfonyQuestionHelper();
        }
        $answer = $this->questionHelper->ask($this->input, $this, $question);
        if ($this->input->isInteractive()) {
            $this->newLine();
            $this->bufferedOutput->write("\n");
        }
        return $answer;
    }
    /**
     * {@inheritdoc}
     * @param int $type
     */
    public function writeln($messages, $type = self::OUTPUT_NORMAL)
    {
        if (!\is_iterable($messages)) {
            $messages = [$messages];
        }
        foreach ($messages as $message) {
            parent::writeln($message, $type);
            $this->writeBuffer($message, \true, $type);
        }
    }
    /**
     * {@inheritdoc}
     * @param bool $newline
     * @param int $type
     */
    public function write($messages, $newline = \false, $type = self::OUTPUT_NORMAL)
    {
        if (!\is_iterable($messages)) {
            $messages = [$messages];
        }
        foreach ($messages as $message) {
            parent::write($message, $newline, $type);
            $this->writeBuffer($message, $newline, $type);
        }
    }
    /**
     * {@inheritdoc}
     * @param int $count
     */
    public function newLine($count = 1)
    {
        parent::newLine($count);
        $this->bufferedOutput->write(\str_repeat("\n", $count));
    }
    /**
     * Returns a new instance which makes use of stderr if available.
     *
     * @return self
     */
    public function getErrorStyle()
    {
        return new self($this->input, $this->getErrorOutput());
    }
    private function getProgressBar() : \RectorPrefix20210705\Symfony\Component\Console\Helper\ProgressBar
    {
        if (!$this->progressBar) {
            throw new \RectorPrefix20210705\Symfony\Component\Console\Exception\RuntimeException('The ProgressBar is not started.');
        }
        return $this->progressBar;
    }
    private function autoPrependBlock() : void
    {
        $chars = \substr(\str_replace(\PHP_EOL, "\n", $this->bufferedOutput->fetch()), -2);
        if (!isset($chars[0])) {
            $this->newLine();
            //empty history, so we should start with a new line.
            return;
        }
        //Prepend new line for each non LF chars (This means no blank line was output before)
        $this->newLine(2 - \substr_count($chars, "\n"));
    }
    private function autoPrependText() : void
    {
        $fetched = $this->bufferedOutput->fetch();
        //Prepend new line if last char isn't EOL:
        if ("\n" !== \substr($fetched, -1)) {
            $this->newLine();
        }
    }
    /**
     * @param string $message
     * @param bool $newLine
     * @param int $type
     */
    private function writeBuffer($message, $newLine, $type) : void
    {
        // We need to know if the last chars are PHP_EOL
        $this->bufferedOutput->write($message, $newLine, $type);
    }
    /**
     * @param mixed[] $messages
     * @param string|null $type
     * @param string|null $style
     * @param string $prefix
     * @param bool $padding
     * @param bool $escape
     */
    private function createBlock($messages, $type = null, $style = null, $prefix = ' ', $padding = \false, $escape = \false) : array
    {
        $indentLength = 0;
        $prefixLength = \RectorPrefix20210705\Symfony\Component\Console\Helper\Helper::width(\RectorPrefix20210705\Symfony\Component\Console\Helper\Helper::removeDecoration($this->getFormatter(), $prefix));
        $lines = [];
        if (null !== $type) {
            $type = \sprintf('[%s] ', $type);
            $indentLength = \strlen($type);
            $lineIndentation = \str_repeat(' ', $indentLength);
        }
        // wrap and add newlines for each element
        foreach ($messages as $key => $message) {
            if ($escape) {
                $message = \RectorPrefix20210705\Symfony\Component\Console\Formatter\OutputFormatter::escape($message);
            }
            $decorationLength = \RectorPrefix20210705\Symfony\Component\Console\Helper\Helper::width($message) - \RectorPrefix20210705\Symfony\Component\Console\Helper\Helper::width(\RectorPrefix20210705\Symfony\Component\Console\Helper\Helper::removeDecoration($this->getFormatter(), $message));
            $messageLineLength = \min($this->lineLength - $prefixLength - $indentLength + $decorationLength, $this->lineLength);
            $messageLines = \explode(\PHP_EOL, \wordwrap($message, $messageLineLength, \PHP_EOL, \true));
            foreach ($messageLines as $messageLine) {
                $lines[] = $messageLine;
            }
            if (\count($messages) > 1 && $key < \count($messages) - 1) {
                $lines[] = '';
            }
        }
        $firstLineIndex = 0;
        if ($padding && $this->isDecorated()) {
            $firstLineIndex = 1;
            \array_unshift($lines, '');
            $lines[] = '';
        }
        foreach ($lines as $i => &$line) {
            if (null !== $type) {
                $line = $firstLineIndex === $i ? $type . $line : $lineIndentation . $line;
            }
            $line = $prefix . $line;
            $line .= \str_repeat(' ', \max($this->lineLength - \RectorPrefix20210705\Symfony\Component\Console\Helper\Helper::width(\RectorPrefix20210705\Symfony\Component\Console\Helper\Helper::removeDecoration($this->getFormatter(), $line)), 0));
            if ($style) {
                $line = \sprintf('<%s>%s</>', $style, $line);
            }
        }
        return $lines;
    }
}
