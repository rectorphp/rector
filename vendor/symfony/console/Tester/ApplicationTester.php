<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RectorPrefix20211020\Symfony\Component\Console\Tester;

use RectorPrefix20211020\Symfony\Component\Console\Application;
use RectorPrefix20211020\Symfony\Component\Console\Input\ArrayInput;
/**
 * Eases the testing of console applications.
 *
 * When testing an application, don't forget to disable the auto exit flag:
 *
 *     $application = new Application();
 *     $application->setAutoExit(false);
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class ApplicationTester
{
    use TesterTrait;
    private $application;
    private $input;
    private $statusCode;
    public function __construct(\RectorPrefix20211020\Symfony\Component\Console\Application $application)
    {
        $this->application = $application;
    }
    /**
     * Executes the application.
     *
     * Available options:
     *
     *  * interactive:               Sets the input interactive flag
     *  * decorated:                 Sets the output decorated flag
     *  * verbosity:                 Sets the output verbosity flag
     *  * capture_stderr_separately: Make output of stdOut and stdErr separately available
     *
     * @return int The command exit code
     * @param mixed[] $input
     * @param mixed[] $options
     */
    public function run($input, $options = [])
    {
        $this->input = new \RectorPrefix20211020\Symfony\Component\Console\Input\ArrayInput($input);
        if (isset($options['interactive'])) {
            $this->input->setInteractive($options['interactive']);
        }
        if ($this->inputs) {
            $this->input->setStream(self::createStream($this->inputs));
        }
        $this->initOutput($options);
        return $this->statusCode = $this->application->run($this->input, $this->output);
    }
}
