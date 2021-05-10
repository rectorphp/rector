<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RectorPrefix20210510\Symfony\Component\VarDumper\Caster;

use RectorPrefix20210510\Ramsey\Uuid\UuidInterface;
use RectorPrefix20210510\Symfony\Component\VarDumper\Cloner\Stub;
/**
 * @author Grégoire Pineau <lyrixx@lyrixx.info>
 */
final class UuidCaster
{
    public static function castRamseyUuid(UuidInterface $c, array $a, Stub $stub, bool $isNested) : array
    {
        $a += [Caster::PREFIX_VIRTUAL . 'uuid' => (string) $c];
        return $a;
    }
}
