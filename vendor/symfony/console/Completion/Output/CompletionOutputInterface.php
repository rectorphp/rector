<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RectorPrefix20220209\Symfony\Component\Console\Completion\Output;

use RectorPrefix20220209\Symfony\Component\Console\Completion\CompletionSuggestions;
use RectorPrefix20220209\Symfony\Component\Console\Output\OutputInterface;
/**
 * Transforms the {@see CompletionSuggestions} object into output readable by the shell completion.
 *
 * @author Wouter de Jong <wouter@wouterj.nl>
 */
interface CompletionOutputInterface
{
    public function write(\RectorPrefix20220209\Symfony\Component\Console\Completion\CompletionSuggestions $suggestions, \RectorPrefix20220209\Symfony\Component\Console\Output\OutputInterface $output) : void;
}
