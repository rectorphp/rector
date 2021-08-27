<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RectorPrefix20210827\Symfony\Component\VarDumper\Caster;

use RectorPrefix20210827\Doctrine\Common\Proxy\Proxy as CommonProxy;
use RectorPrefix20210827\Doctrine\ORM\PersistentCollection;
use RectorPrefix20210827\Doctrine\ORM\Proxy\Proxy as OrmProxy;
use RectorPrefix20210827\Symfony\Component\VarDumper\Cloner\Stub;
/**
 * Casts Doctrine related classes to array representation.
 *
 * @author Nicolas Grekas <p@tchwork.com>
 *
 * @final
 */
class DoctrineCaster
{
    public static function castCommonProxy(\RectorPrefix20210827\Doctrine\Common\Proxy\Proxy $proxy, array $a, \RectorPrefix20210827\Symfony\Component\VarDumper\Cloner\Stub $stub, bool $isNested)
    {
        foreach (['__cloner__', '__initializer__'] as $k) {
            if (\array_key_exists($k, $a)) {
                unset($a[$k]);
                ++$stub->cut;
            }
        }
        return $a;
    }
    public static function castOrmProxy(\RectorPrefix20210827\Doctrine\ORM\Proxy\Proxy $proxy, array $a, \RectorPrefix20210827\Symfony\Component\VarDumper\Cloner\Stub $stub, bool $isNested)
    {
        foreach (['_entityPersister', '_identifier'] as $k) {
            if (\array_key_exists($k = "\0Doctrine\\ORM\\Proxy\\Proxy\0" . $k, $a)) {
                unset($a[$k]);
                ++$stub->cut;
            }
        }
        return $a;
    }
    public static function castPersistentCollection(\RectorPrefix20210827\Doctrine\ORM\PersistentCollection $coll, array $a, \RectorPrefix20210827\Symfony\Component\VarDumper\Cloner\Stub $stub, bool $isNested)
    {
        foreach (['snapshot', 'association', 'typeClass'] as $k) {
            if (\array_key_exists($k = "\0Doctrine\\ORM\\PersistentCollection\0" . $k, $a)) {
                $a[$k] = new \RectorPrefix20210827\Symfony\Component\VarDumper\Caster\CutStub($a[$k]);
            }
        }
        return $a;
    }
}
