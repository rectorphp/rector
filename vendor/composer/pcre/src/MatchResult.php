<?php

/*
 * This file is part of composer/pcre.
 *
 * (c) Composer <https://github.com/composer>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */
namespace RectorPrefix20211231\Composer\Pcre;

final class MatchResult
{
    /**
     * An array of match group => string matched
     *
     * @readonly
     * @var array<int|string, string|null>
     */
    public $matches;
    /**
     * @readonly
     * @var bool
     */
    public $matched;
    /**
     * @param int $count
     * @param array<string|null> $matches
     */
    public function __construct($count, array $matches)
    {
        $this->matches = $matches;
        $this->matched = (bool) $count;
    }
}
