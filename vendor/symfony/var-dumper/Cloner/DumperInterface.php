<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RectorPrefix20211020\Symfony\Component\VarDumper\Cloner;

/**
 * DumperInterface used by Data objects.
 *
 * @author Nicolas Grekas <p@tchwork.com>
 */
interface DumperInterface
{
    /**
     * Dumps a scalar value.
     *
     * @param string                $type  The PHP type of the value being dumped
     * @param string|int|float|bool $value The scalar value being dumped
     * @param \Symfony\Component\VarDumper\Cloner\Cursor $cursor
     */
    public function dumpScalar($cursor, $type, $value);
    /**
     * Dumps a string.
     *
     * @param string $str The string being dumped
     * @param bool   $bin Whether $str is UTF-8 or binary encoded
     * @param int    $cut The number of characters $str has been cut by
     * @param \Symfony\Component\VarDumper\Cloner\Cursor $cursor
     */
    public function dumpString($cursor, $str, $bin, $cut);
    /**
     * Dumps while entering an hash.
     *
     * @param int        $type     A Cursor::HASH_* const for the type of hash
     * @param string|int $class    The object class, resource type or array count
     * @param bool       $hasChild When the dump of the hash has child item
     * @param \Symfony\Component\VarDumper\Cloner\Cursor $cursor
     */
    public function enterHash($cursor, $type, $class, $hasChild);
    /**
     * Dumps while leaving an hash.
     *
     * @param int        $type     A Cursor::HASH_* const for the type of hash
     * @param string|int $class    The object class, resource type or array count
     * @param bool       $hasChild When the dump of the hash has child item
     * @param int        $cut      The number of items the hash has been cut by
     * @param \Symfony\Component\VarDumper\Cloner\Cursor $cursor
     */
    public function leaveHash($cursor, $type, $class, $hasChild, $cut);
}
