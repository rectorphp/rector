<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RectorPrefix20210510\Symfony\Component\Console\Event;

use RectorPrefix20210510\Symfony\Component\Console\Command\Command;
use RectorPrefix20210510\Symfony\Component\Console\Input\InputInterface;
use RectorPrefix20210510\Symfony\Component\Console\Output\OutputInterface;
/**
 * @author marie <marie@users.noreply.github.com>
 */
final class ConsoleSignalEvent extends ConsoleEvent
{
    private $handlingSignal;
    public function __construct(Command $command, InputInterface $input, OutputInterface $output, int $handlingSignal)
    {
        parent::__construct($command, $input, $output);
        $this->handlingSignal = $handlingSignal;
    }
    public function getHandlingSignal() : int
    {
        return $this->handlingSignal;
    }
}
