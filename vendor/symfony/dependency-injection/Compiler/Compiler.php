<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RectorPrefix20220501\Symfony\Component\DependencyInjection\Compiler;

use RectorPrefix20220501\Symfony\Component\DependencyInjection\ContainerBuilder;
use RectorPrefix20220501\Symfony\Component\DependencyInjection\Exception\EnvParameterException;
/**
 * This class is used to remove circular dependencies between individual passes.
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
class Compiler
{
    private $passConfig;
    /**
     * @var mixed[]
     */
    private $log = [];
    private $serviceReferenceGraph;
    public function __construct()
    {
        $this->passConfig = new \RectorPrefix20220501\Symfony\Component\DependencyInjection\Compiler\PassConfig();
        $this->serviceReferenceGraph = new \RectorPrefix20220501\Symfony\Component\DependencyInjection\Compiler\ServiceReferenceGraph();
    }
    public function getPassConfig() : \RectorPrefix20220501\Symfony\Component\DependencyInjection\Compiler\PassConfig
    {
        return $this->passConfig;
    }
    public function getServiceReferenceGraph() : \RectorPrefix20220501\Symfony\Component\DependencyInjection\Compiler\ServiceReferenceGraph
    {
        return $this->serviceReferenceGraph;
    }
    public function addPass(\RectorPrefix20220501\Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface $pass, string $type = \RectorPrefix20220501\Symfony\Component\DependencyInjection\Compiler\PassConfig::TYPE_BEFORE_OPTIMIZATION, int $priority = 0)
    {
        $this->passConfig->addPass($pass, $type, $priority);
    }
    /**
     * @final
     */
    public function log(\RectorPrefix20220501\Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface $pass, string $message)
    {
        if (\strpos($message, "\n") !== \false) {
            $message = \str_replace("\n", "\n" . \get_class($pass) . ': ', \trim($message));
        }
        $this->log[] = \get_class($pass) . ': ' . $message;
    }
    public function getLog() : array
    {
        return $this->log;
    }
    /**
     * Run the Compiler and process all Passes.
     */
    public function compile(\RectorPrefix20220501\Symfony\Component\DependencyInjection\ContainerBuilder $container)
    {
        try {
            foreach ($this->passConfig->getPasses() as $pass) {
                $pass->process($container);
            }
        } catch (\Exception $e) {
            $usedEnvs = [];
            $prev = $e;
            do {
                $msg = $prev->getMessage();
                if ($msg !== ($resolvedMsg = $container->resolveEnvPlaceholders($msg, null, $usedEnvs))) {
                    $r = new \ReflectionProperty($prev, 'message');
                    $r->setAccessible(\true);
                    $r->setValue($prev, $resolvedMsg);
                }
            } while ($prev = $prev->getPrevious());
            if ($usedEnvs) {
                $e = new \RectorPrefix20220501\Symfony\Component\DependencyInjection\Exception\EnvParameterException($usedEnvs, $e);
            }
            throw $e;
        } finally {
            $this->getServiceReferenceGraph()->clear();
        }
    }
}
