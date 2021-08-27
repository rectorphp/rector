<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RectorPrefix20210827\Symfony\Component\VarDumper\Command\Descriptor;

use RectorPrefix20210827\Symfony\Component\Console\Output\OutputInterface;
use RectorPrefix20210827\Symfony\Component\VarDumper\Cloner\Data;
/**
 * @author Maxime Steinhausser <maxime.steinhausser@gmail.com>
 */
interface DumpDescriptorInterface
{
    /**
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @param \Symfony\Component\VarDumper\Cloner\Data $data
     * @param mixed[] $context
     * @param int $clientId
     */
    public function describe($output, $data, $context, $clientId) : void;
}
