<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RectorPrefix20220501\Symfony\Component\Console\Helper;

use RectorPrefix20220501\Symfony\Component\Console\Formatter\OutputFormatter;
use RectorPrefix20220501\Symfony\Component\Console\Output\OutputInterface;
use RectorPrefix20220501\Symfony\Component\Console\Question\ChoiceQuestion;
use RectorPrefix20220501\Symfony\Component\Console\Question\ConfirmationQuestion;
use RectorPrefix20220501\Symfony\Component\Console\Question\Question;
use RectorPrefix20220501\Symfony\Component\Console\Style\SymfonyStyle;
/**
 * Symfony Style Guide compliant question helper.
 *
 * @author Kevin Bond <kevinbond@gmail.com>
 */
class SymfonyQuestionHelper extends \RectorPrefix20220501\Symfony\Component\Console\Helper\QuestionHelper
{
    /**
     * {@inheritdoc}
     */
    protected function writePrompt(\RectorPrefix20220501\Symfony\Component\Console\Output\OutputInterface $output, \RectorPrefix20220501\Symfony\Component\Console\Question\Question $question)
    {
        $text = \RectorPrefix20220501\Symfony\Component\Console\Formatter\OutputFormatter::escapeTrailingBackslash($question->getQuestion());
        $default = $question->getDefault();
        if ($question->isMultiline()) {
            $text .= \sprintf(' (press %s to continue)', $this->getEofShortcut());
        }
        switch (\true) {
            case null === $default:
                $text = \sprintf(' <info>%s</info>:', $text);
                break;
            case $question instanceof \RectorPrefix20220501\Symfony\Component\Console\Question\ConfirmationQuestion:
                $text = \sprintf(' <info>%s (yes/no)</info> [<comment>%s</comment>]:', $text, $default ? 'yes' : 'no');
                break;
            case $question instanceof \RectorPrefix20220501\Symfony\Component\Console\Question\ChoiceQuestion && $question->isMultiselect():
                $choices = $question->getChoices();
                $default = \explode(',', $default);
                foreach ($default as $key => $value) {
                    $default[$key] = $choices[\trim($value)];
                }
                $text = \sprintf(' <info>%s</info> [<comment>%s</comment>]:', $text, \RectorPrefix20220501\Symfony\Component\Console\Formatter\OutputFormatter::escape(\implode(', ', $default)));
                break;
            case $question instanceof \RectorPrefix20220501\Symfony\Component\Console\Question\ChoiceQuestion:
                $choices = $question->getChoices();
                $text = \sprintf(' <info>%s</info> [<comment>%s</comment>]:', $text, \RectorPrefix20220501\Symfony\Component\Console\Formatter\OutputFormatter::escape($choices[$default] ?? $default));
                break;
            default:
                $text = \sprintf(' <info>%s</info> [<comment>%s</comment>]:', $text, \RectorPrefix20220501\Symfony\Component\Console\Formatter\OutputFormatter::escape($default));
        }
        $output->writeln($text);
        $prompt = ' > ';
        if ($question instanceof \RectorPrefix20220501\Symfony\Component\Console\Question\ChoiceQuestion) {
            $output->writeln($this->formatChoiceQuestionChoices($question, 'comment'));
            $prompt = $question->getPrompt();
        }
        $output->write($prompt);
    }
    /**
     * {@inheritdoc}
     */
    protected function writeError(\RectorPrefix20220501\Symfony\Component\Console\Output\OutputInterface $output, \Exception $error)
    {
        if ($output instanceof \RectorPrefix20220501\Symfony\Component\Console\Style\SymfonyStyle) {
            $output->newLine();
            $output->error($error->getMessage());
            return;
        }
        parent::writeError($output, $error);
    }
    private function getEofShortcut() : string
    {
        if ('Windows' === \PHP_OS_FAMILY) {
            return '<comment>Ctrl+Z</comment> then <comment>Enter</comment>';
        }
        return '<comment>Ctrl+D</comment>';
    }
}
