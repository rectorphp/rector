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

final class ReplaceResult
{
    /**
     * @readonly
     * @var string
     */
    public $result;
    /**
     * @readonly
     * @var int
     */
    public $count;
    /**
     * @readonly
     * @var bool
     */
    public $matched;
    /**
     * @param int $count
     * @param string $result
     */
    public function __construct($count, $result)
    {
        $this->count = $count;
        $this->matched = (bool) $count;
        $this->result = $result;
    }
}
