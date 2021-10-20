<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RectorPrefix20211020\Symfony\Component\HttpKernel\DataCollector;

use RectorPrefix20211020\Symfony\Component\VarDumper\Caster\CutStub;
use RectorPrefix20211020\Symfony\Component\VarDumper\Caster\ReflectionCaster;
use RectorPrefix20211020\Symfony\Component\VarDumper\Cloner\ClonerInterface;
use RectorPrefix20211020\Symfony\Component\VarDumper\Cloner\Data;
use RectorPrefix20211020\Symfony\Component\VarDumper\Cloner\Stub;
use RectorPrefix20211020\Symfony\Component\VarDumper\Cloner\VarCloner;
/**
 * DataCollector.
 *
 * Children of this class must store the collected data in the data property.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 * @author Bernhard Schussek <bschussek@symfony.com>
 */
abstract class DataCollector implements \RectorPrefix20211020\Symfony\Component\HttpKernel\DataCollector\DataCollectorInterface
{
    /**
     * @var array|Data
     */
    protected $data = [];
    /**
     * @var ClonerInterface
     */
    private $cloner;
    /**
     * Converts the variable into a serializable Data instance.
     *
     * This array can be displayed in the template using
     * the VarDumper component.
     *
     * @param mixed $var
     *
     * @return Data
     */
    protected function cloneVar($var)
    {
        if ($var instanceof \RectorPrefix20211020\Symfony\Component\VarDumper\Cloner\Data) {
            return $var;
        }
        if (null === $this->cloner) {
            $this->cloner = new \RectorPrefix20211020\Symfony\Component\VarDumper\Cloner\VarCloner();
            $this->cloner->setMaxItems(-1);
            $this->cloner->addCasters($this->getCasters());
        }
        return $this->cloner->cloneVar($var);
    }
    /**
     * @return callable[] The casters to add to the cloner
     */
    protected function getCasters()
    {
        $casters = ['*' => function ($v, array $a, \RectorPrefix20211020\Symfony\Component\VarDumper\Cloner\Stub $s, $isNested) {
            if (!$v instanceof \RectorPrefix20211020\Symfony\Component\VarDumper\Cloner\Stub) {
                foreach ($a as $k => $v) {
                    if (\is_object($v) && !$v instanceof \DateTimeInterface && !$v instanceof \RectorPrefix20211020\Symfony\Component\VarDumper\Cloner\Stub) {
                        $a[$k] = new \RectorPrefix20211020\Symfony\Component\VarDumper\Caster\CutStub($v);
                    }
                }
            }
            return $a;
        }] + \RectorPrefix20211020\Symfony\Component\VarDumper\Caster\ReflectionCaster::UNSET_CLOSURE_FILE_INFO;
        return $casters;
    }
    /**
     * @return array
     */
    public function __sleep()
    {
        return ['data'];
    }
    public function __wakeup()
    {
    }
    /**
     * @internal to prevent implementing \Serializable
     */
    protected final function serialize()
    {
    }
    /**
     * @internal to prevent implementing \Serializable
     */
    protected final function unserialize($data)
    {
    }
}
