<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RectorPrefix202407\Symfony\Component\Console\Debug;

use RectorPrefix202407\Symfony\Component\Console\Command\TraceableCommand;
use RectorPrefix202407\Symfony\Component\HttpFoundation\Request;
use RectorPrefix202407\Symfony\Component\HttpFoundation\Response;
/**
 * @internal
 */
final class CliRequest extends Request
{
    /**
     * @readonly
     * @var \Symfony\Component\Console\Command\TraceableCommand
     */
    public $command;
    public function __construct(TraceableCommand $command)
    {
        $this->command = $command;
        parent::__construct(attributes: ['_controller' => \get_class($command->command), '_virtual_type' => 'command'], server: $_SERVER);
    }
    // Methods below allow to populate a profile, thus enable search and filtering
    public function getUri() : string
    {
        if ($this->server->has('SYMFONY_CLI_BINARY_NAME')) {
            $binary = $this->server->get('SYMFONY_CLI_BINARY_NAME') . ' console';
        } else {
            $binary = $this->server->get('argv')[0];
        }
        return $binary . ' ' . $this->command->input;
    }
    public function getMethod() : string
    {
        return $this->command->isInteractive ? 'INTERACTIVE' : 'BATCH';
    }
    public function getResponse() : Response
    {
        return new class($this->command->exitCode) extends Response
        {
            /**
             * @readonly
             * @var int
             */
            private $exitCode;
            public function __construct(int $exitCode)
            {
                $this->exitCode = $exitCode;
                parent::__construct();
            }
            public function getStatusCode() : int
            {
                return $this->exitCode;
            }
        };
    }
    public function getClientIp() : string
    {
        $application = $this->command->getApplication();
        return $application->getName() . ' ' . $application->getVersion();
    }
}
