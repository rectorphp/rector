<?php

/**
 * This file is part of the Tracy (https://tracy.nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */
declare (strict_types=1);
namespace RectorPrefix20211231\Tracy\Bridges\Nette;

use RectorPrefix20211231\Nette;
use RectorPrefix20211231\Nette\Schema\Expect;
use RectorPrefix20211231\Tracy;
/**
 * Tracy extension for Nette DI.
 */
class TracyExtension extends \RectorPrefix20211231\Nette\DI\CompilerExtension
{
    private const ERROR_SEVERITY_PATTERN = 'E_(?:ALL|PARSE|STRICT|RECOVERABLE_ERROR|(?:CORE|COMPILE)_(?:ERROR|WARNING)|(?:USER_)?(?:ERROR|WARNING|NOTICE|DEPRECATED))';
    /** @var bool */
    private $debugMode;
    /** @var bool */
    private $cliMode;
    public function __construct(bool $debugMode = \false, bool $cliMode = \false)
    {
        $this->debugMode = $debugMode;
        $this->cliMode = $cliMode;
    }
    public function getConfigSchema() : \RectorPrefix20211231\Nette\Schema\Schema
    {
        $errorSeverity = \RectorPrefix20211231\Nette\Schema\Expect::string()->pattern(self::ERROR_SEVERITY_PATTERN);
        $errorSeverityExpr = \RectorPrefix20211231\Nette\Schema\Expect::string()->pattern('(' . self::ERROR_SEVERITY_PATTERN . '|[ &|~()])+');
        return \RectorPrefix20211231\Nette\Schema\Expect::structure(['email' => \RectorPrefix20211231\Nette\Schema\Expect::anyOf(\RectorPrefix20211231\Nette\Schema\Expect::email(), \RectorPrefix20211231\Nette\Schema\Expect::listOf('email'))->dynamic(), 'fromEmail' => \RectorPrefix20211231\Nette\Schema\Expect::email()->dynamic(), 'emailSnooze' => \RectorPrefix20211231\Nette\Schema\Expect::string()->dynamic(), 'logSeverity' => \RectorPrefix20211231\Nette\Schema\Expect::anyOf(\RectorPrefix20211231\Nette\Schema\Expect::int(), $errorSeverityExpr, \RectorPrefix20211231\Nette\Schema\Expect::listOf($errorSeverity)), 'editor' => \RectorPrefix20211231\Nette\Schema\Expect::string()->dynamic(), 'browser' => \RectorPrefix20211231\Nette\Schema\Expect::string()->dynamic(), 'errorTemplate' => \RectorPrefix20211231\Nette\Schema\Expect::string()->dynamic(), 'strictMode' => \RectorPrefix20211231\Nette\Schema\Expect::anyOf(\RectorPrefix20211231\Nette\Schema\Expect::bool(), \RectorPrefix20211231\Nette\Schema\Expect::int(), $errorSeverityExpr, \RectorPrefix20211231\Nette\Schema\Expect::listOf($errorSeverity)), 'showBar' => \RectorPrefix20211231\Nette\Schema\Expect::bool()->dynamic(), 'maxLength' => \RectorPrefix20211231\Nette\Schema\Expect::int()->dynamic(), 'maxDepth' => \RectorPrefix20211231\Nette\Schema\Expect::int()->dynamic(), 'keysToHide' => \RectorPrefix20211231\Nette\Schema\Expect::array(null)->dynamic(), 'dumpTheme' => \RectorPrefix20211231\Nette\Schema\Expect::string()->dynamic(), 'showLocation' => \RectorPrefix20211231\Nette\Schema\Expect::bool()->dynamic(), 'scream' => \RectorPrefix20211231\Nette\Schema\Expect::anyOf(\RectorPrefix20211231\Nette\Schema\Expect::bool(), \RectorPrefix20211231\Nette\Schema\Expect::int(), $errorSeverityExpr, \RectorPrefix20211231\Nette\Schema\Expect::listOf($errorSeverity)), 'bar' => \RectorPrefix20211231\Nette\Schema\Expect::listOf('RectorPrefix20211231\\string|Nette\\DI\\Definitions\\Statement'), 'blueScreen' => \RectorPrefix20211231\Nette\Schema\Expect::listOf('callable'), 'editorMapping' => \RectorPrefix20211231\Nette\Schema\Expect::arrayOf('string')->dynamic()->default(null), 'netteMailer' => \RectorPrefix20211231\Nette\Schema\Expect::bool(\true)]);
    }
    public function loadConfiguration()
    {
        $builder = $this->getContainerBuilder();
        $builder->addDefinition($this->prefix('logger'))->setClass(\RectorPrefix20211231\Tracy\ILogger::class)->setFactory([\RectorPrefix20211231\Tracy\Debugger::class, 'getLogger']);
        $builder->addDefinition($this->prefix('blueScreen'))->setFactory([\RectorPrefix20211231\Tracy\Debugger::class, 'getBlueScreen']);
        $builder->addDefinition($this->prefix('bar'))->setFactory([\RectorPrefix20211231\Tracy\Debugger::class, 'getBar']);
    }
    public function afterCompile(\RectorPrefix20211231\Nette\PhpGenerator\ClassType $class)
    {
        $initialize = $this->initialization ?? new \RectorPrefix20211231\Nette\PhpGenerator\Closure();
        $initialize->addBody('if (!Tracy\\Debugger::isEnabled()) { return; }');
        $builder = $this->getContainerBuilder();
        $options = (array) $this->config;
        unset($options['bar'], $options['blueScreen'], $options['netteMailer']);
        foreach (['logSeverity', 'strictMode', 'scream'] as $key) {
            if (\is_string($options[$key]) || \is_array($options[$key])) {
                $options[$key] = $this->parseErrorSeverity($options[$key]);
            }
        }
        foreach ($options as $key => $value) {
            if ($value !== null) {
                static $tbl = ['keysToHide' => 'array_push(Tracy\\Debugger::getBlueScreen()->keysToHide, ... ?)', 'fromEmail' => 'Tracy\\Debugger::getLogger()->fromEmail = ?', 'emailSnooze' => 'Tracy\\Debugger::getLogger()->emailSnooze = ?'];
                $initialize->addBody($builder->formatPhp(($tbl[$key] ?? 'Tracy\\Debugger::$' . $key . ' = ?') . ';', \RectorPrefix20211231\Nette\DI\Helpers::filterArguments([$value])));
            }
        }
        $logger = $builder->getDefinition($this->prefix('logger'));
        if (!$logger instanceof \RectorPrefix20211231\Nette\DI\ServiceDefinition || $logger->getFactory()->getEntity() !== [\RectorPrefix20211231\Tracy\Debugger::class, 'getLogger']) {
            $initialize->addBody($builder->formatPhp('Tracy\\Debugger::setLogger(?);', [$logger]));
        }
        if ($this->config->netteMailer && $builder->getByType(\RectorPrefix20211231\Nette\Mail\IMailer::class)) {
            $initialize->addBody($builder->formatPhp('Tracy\\Debugger::getLogger()->mailer = ?;', [[new \RectorPrefix20211231\Nette\DI\Statement(\RectorPrefix20211231\Tracy\Bridges\Nette\MailSender::class, ['fromEmail' => $this->config->fromEmail]), 'send']]));
        }
        if ($this->debugMode) {
            foreach ($this->config->bar as $item) {
                if (\is_string($item) && \substr($item, 0, 1) === '@') {
                    $item = new \RectorPrefix20211231\Nette\DI\Statement(['@' . $builder::THIS_CONTAINER, 'getService'], [\substr($item, 1)]);
                } elseif (\is_string($item)) {
                    $item = new \RectorPrefix20211231\Nette\DI\Statement($item);
                }
                $initialize->addBody($builder->formatPhp('$this->getService(?)->addPanel(?);', \RectorPrefix20211231\Nette\DI\Helpers::filterArguments([$this->prefix('bar'), $item])));
            }
            if (!$this->cliMode && \RectorPrefix20211231\Tracy\Debugger::getSessionStorage() instanceof \RectorPrefix20211231\Tracy\NativeSession && ($name = $builder->getByType(\RectorPrefix20211231\Nette\Http\Session::class))) {
                $initialize->addBody('$this->getService(?)->start();', [$name]);
                $initialize->addBody('Tracy\\Debugger::dispatch();');
            }
        }
        foreach ($this->config->blueScreen as $item) {
            $initialize->addBody($builder->formatPhp('$this->getService(?)->addPanel(?);', \RectorPrefix20211231\Nette\DI\Helpers::filterArguments([$this->prefix('blueScreen'), $item])));
        }
        if (empty($this->initialization)) {
            $class->getMethod('initialize')->addBody("({$initialize})();");
        }
        if (($dir = \RectorPrefix20211231\Tracy\Debugger::$logDirectory) && !\is_writable($dir)) {
            throw new \RectorPrefix20211231\Nette\InvalidStateException("Make directory '{$dir}' writable.");
        }
    }
    /**
     * @param  string|string[]  $value
     */
    private function parseErrorSeverity($value) : int
    {
        $value = \implode('|', (array) $value);
        $res = (int) @\parse_ini_string('e = ' . $value)['e'];
        // @ may fail
        if (!$res) {
            throw new \RectorPrefix20211231\Nette\InvalidStateException("Syntax error in expression '{$value}'");
        }
        return $res;
    }
}
