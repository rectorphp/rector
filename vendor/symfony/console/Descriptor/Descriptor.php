<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RectorPrefix20220418\Symfony\Component\Console\Descriptor;

use RectorPrefix20220418\Symfony\Component\Console\Application;
use RectorPrefix20220418\Symfony\Component\Console\Command\Command;
use RectorPrefix20220418\Symfony\Component\Console\Exception\InvalidArgumentException;
use RectorPrefix20220418\Symfony\Component\Console\Input\InputArgument;
use RectorPrefix20220418\Symfony\Component\Console\Input\InputDefinition;
use RectorPrefix20220418\Symfony\Component\Console\Input\InputOption;
use RectorPrefix20220418\Symfony\Component\Console\Output\OutputInterface;
/**
 * @author Jean-François Simon <jeanfrancois.simon@sensiolabs.com>
 *
 * @internal
 */
abstract class Descriptor implements \RectorPrefix20220418\Symfony\Component\Console\Descriptor\DescriptorInterface
{
    /**
     * @var OutputInterface
     */
    protected $output;
    /**
     * {@inheritdoc}
     */
    public function describe(\RectorPrefix20220418\Symfony\Component\Console\Output\OutputInterface $output, object $object, array $options = [])
    {
        $this->output = $output;
        switch (\true) {
            case $object instanceof \RectorPrefix20220418\Symfony\Component\Console\Input\InputArgument:
                $this->describeInputArgument($object, $options);
                break;
            case $object instanceof \RectorPrefix20220418\Symfony\Component\Console\Input\InputOption:
                $this->describeInputOption($object, $options);
                break;
            case $object instanceof \RectorPrefix20220418\Symfony\Component\Console\Input\InputDefinition:
                $this->describeInputDefinition($object, $options);
                break;
            case $object instanceof \RectorPrefix20220418\Symfony\Component\Console\Command\Command:
                $this->describeCommand($object, $options);
                break;
            case $object instanceof \RectorPrefix20220418\Symfony\Component\Console\Application:
                $this->describeApplication($object, $options);
                break;
            default:
                throw new \RectorPrefix20220418\Symfony\Component\Console\Exception\InvalidArgumentException(\sprintf('Object of type "%s" is not describable.', \get_debug_type($object)));
        }
    }
    /**
     * Writes content to output.
     */
    protected function write(string $content, bool $decorated = \false)
    {
        $this->output->write($content, \false, $decorated ? \RectorPrefix20220418\Symfony\Component\Console\Output\OutputInterface::OUTPUT_NORMAL : \RectorPrefix20220418\Symfony\Component\Console\Output\OutputInterface::OUTPUT_RAW);
    }
    /**
     * Describes an InputArgument instance.
     */
    protected abstract function describeInputArgument(\RectorPrefix20220418\Symfony\Component\Console\Input\InputArgument $argument, array $options = []);
    /**
     * Describes an InputOption instance.
     */
    protected abstract function describeInputOption(\RectorPrefix20220418\Symfony\Component\Console\Input\InputOption $option, array $options = []);
    /**
     * Describes an InputDefinition instance.
     */
    protected abstract function describeInputDefinition(\RectorPrefix20220418\Symfony\Component\Console\Input\InputDefinition $definition, array $options = []);
    /**
     * Describes a Command instance.
     */
    protected abstract function describeCommand(\RectorPrefix20220418\Symfony\Component\Console\Command\Command $command, array $options = []);
    /**
     * Describes an Application instance.
     */
    protected abstract function describeApplication(\RectorPrefix20220418\Symfony\Component\Console\Application $application, array $options = []);
}
