<?php

/**
 * This file is part of the Tracy (https://tracy.nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */
declare (strict_types=1);
namespace RectorPrefix20210510\Tracy\Bridges\Nette;

use RectorPrefix20210510\Nette;
use RectorPrefix20210510\Nette\Schema\Expect;
use RectorPrefix20210510\Tracy;
/**
 * Tracy extension for Nette DI.
 */
class TracyExtension extends Nette\DI\CompilerExtension
{
    /** @var bool */
    private $debugMode;
    /** @var bool */
    private $cliMode;
    public function __construct(bool $debugMode = \false, bool $cliMode = \false)
    {
        $this->debugMode = $debugMode;
        $this->cliMode = $cliMode;
    }
    public function getConfigSchema() : Nette\Schema\Schema
    {
        return Expect::structure(['email' => Expect::anyOf(Expect::email(), Expect::listOf('email'))->dynamic(), 'fromEmail' => Expect::email()->dynamic(), 'logSeverity' => Expect::anyOf(Expect::scalar(), Expect::listOf('scalar')), 'editor' => Expect::string()->dynamic(), 'browser' => Expect::string()->dynamic(), 'errorTemplate' => Expect::string()->dynamic(), 'strictMode' => Expect::bool()->dynamic(), 'showBar' => Expect::bool()->dynamic(), 'maxLength' => Expect::int()->dynamic(), 'maxDepth' => Expect::int()->dynamic(), 'keysToHide' => Expect::array(null)->dynamic(), 'dumpTheme' => Expect::string()->dynamic(), 'showLocation' => Expect::bool()->dynamic(), 'scream' => Expect::bool()->dynamic(), 'bar' => Expect::listOf('RectorPrefix20210510\\string|Nette\\DI\\Definitions\\Statement'), 'blueScreen' => Expect::listOf('callable'), 'editorMapping' => Expect::arrayOf('string')->dynamic()->default(null), 'netteMailer' => Expect::bool(\true)]);
    }
    public function loadConfiguration()
    {
        $builder = $this->getContainerBuilder();
        $builder->addDefinition($this->prefix('logger'))->setClass(Tracy\ILogger::class)->setFactory([Tracy\Debugger::class, 'getLogger']);
        $builder->addDefinition($this->prefix('blueScreen'))->setFactory([Tracy\Debugger::class, 'getBlueScreen']);
        $builder->addDefinition($this->prefix('bar'))->setFactory([Tracy\Debugger::class, 'getBar']);
    }
    public function afterCompile(Nette\PhpGenerator\ClassType $class)
    {
        $initialize = $this->initialization ?? new Nette\PhpGenerator\Closure();
        $initialize->addBody('if (!Tracy\\Debugger::isEnabled()) { return; }');
        $builder = $this->getContainerBuilder();
        $options = (array) $this->config;
        unset($options['bar'], $options['blueScreen'], $options['netteMailer']);
        if (isset($options['logSeverity'])) {
            $res = 0;
            foreach ((array) $options['logSeverity'] as $level) {
                $res |= \is_int($level) ? $level : \constant($level);
            }
            $options['logSeverity'] = $res;
        }
        foreach ($options as $key => $value) {
            if ($value !== null) {
                static $tbl = ['keysToHide' => 'array_push(Tracy\\Debugger::getBlueScreen()->keysToHide, ... ?)', 'fromEmail' => 'Tracy\\Debugger::getLogger()->fromEmail = ?'];
                $initialize->addBody($builder->formatPhp(($tbl[$key] ?? 'Tracy\\Debugger::$' . $key . ' = ?') . ';', Nette\DI\Helpers::filterArguments([$value])));
            }
        }
        $logger = $builder->getDefinition($this->prefix('logger'));
        if (!$logger instanceof Nette\DI\ServiceDefinition || $logger->getFactory()->getEntity() !== [Tracy\Debugger::class, 'getLogger']) {
            $initialize->addBody($builder->formatPhp('Tracy\\Debugger::setLogger(?);', [$logger]));
        }
        if ($this->config->netteMailer && $builder->getByType(Nette\Mail\IMailer::class)) {
            $initialize->addBody($builder->formatPhp('Tracy\\Debugger::getLogger()->mailer = ?;', [[new Nette\DI\Statement(Tracy\Bridges\Nette\MailSender::class, ['fromEmail' => $this->config->fromEmail]), 'send']]));
        }
        if ($this->debugMode) {
            foreach ($this->config->bar as $item) {
                if (\is_string($item) && \substr($item, 0, 1) === '@') {
                    $item = new Nette\DI\Statement(['@' . $builder::THIS_CONTAINER, 'getService'], [\substr($item, 1)]);
                } elseif (\is_string($item)) {
                    $item = new Nette\DI\Statement($item);
                }
                $initialize->addBody($builder->formatPhp('$this->getService(?)->addPanel(?);', Nette\DI\Helpers::filterArguments([$this->prefix('bar'), $item])));
            }
            if (!$this->cliMode && ($name = $builder->getByType(Nette\Http\Session::class))) {
                $initialize->addBody('$this->getService(?)->start();', [$name]);
                $initialize->addBody('Tracy\\Debugger::dispatch();');
            }
        }
        foreach ($this->config->blueScreen as $item) {
            $initialize->addBody($builder->formatPhp('$this->getService(?)->addPanel(?);', Nette\DI\Helpers::filterArguments([$this->prefix('blueScreen'), $item])));
        }
        if (empty($this->initialization)) {
            $class->getMethod('initialize')->addBody("({$initialize})();");
        }
        if (($dir = Tracy\Debugger::$logDirectory) && !\is_writable($dir)) {
            throw new Nette\InvalidStateException("Make directory '{$dir}' writable.");
        }
    }
}
