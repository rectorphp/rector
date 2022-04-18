<?php

/**
 * This file is part of the Tracy (https://tracy.nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */
declare (strict_types=1);
namespace RectorPrefix20220418\Tracy;

use ErrorException;
/**
 * Debugger: displays and logs errors.
 */
class Debugger
{
    public const VERSION = '2.9.1';
    /** server modes for Debugger::enable() */
    public const DEVELOPMENT = \false, PRODUCTION = \true, DETECT = null;
    public const COOKIE_SECRET = 'tracy-debug';
    /** @var bool in production mode is suppressed any debugging output */
    public static $productionMode = self::DETECT;
    /** @var bool whether to display debug bar in development mode */
    public static $showBar = \true;
    /** @var bool whether to send data to FireLogger in development mode */
    public static $showFireLogger = \true;
    /** @var int size of reserved memory */
    public static $reservedMemorySize = 500000;
    /** @var bool */
    private static $enabled = \false;
    /** @var string|null reserved memory; also prevents double rendering */
    private static $reserved;
    /** @var int initial output buffer level */
    private static $obLevel;
    /** @var ?array output buffer status @internal */
    public static $obStatus;
    /********************* errors and exceptions reporting ****************d*g**/
    /** @var bool|int determines whether any error will cause immediate death in development mode; if integer that it's matched against error severity */
    public static $strictMode = \false;
    /** @var bool|int disables the @ (shut-up) operator so that notices and warnings are no longer hidden; if integer than it's matched against error severity */
    public static $scream = \false;
    /** @var callable[] functions that are automatically called after fatal error */
    public static $onFatalError = [];
    /********************* Debugger::dump() ****************d*g**/
    /** @var int  how many nested levels of array/object properties display by dump() */
    public static $maxDepth = 15;
    /** @var int  how long strings display by dump() */
    public static $maxLength = 150;
    /** @var int  how many items in array/object display by dump() */
    public static $maxItems = 100;
    /** @var bool display location by dump()? */
    public static $showLocation;
    /** @var string[] sensitive keys not displayed by dump() */
    public static $keysToHide = [];
    /** @var string theme for dump() */
    public static $dumpTheme = 'light';
    /** @deprecated */
    public static $maxLen;
    /********************* logging ****************d*g**/
    /** @var string|null name of the directory where errors should be logged */
    public static $logDirectory;
    /** @var int  log bluescreen in production mode for this error severity */
    public static $logSeverity = 0;
    /** @var string|array email(s) to which send error notifications */
    public static $email;
    /** for Debugger::log() and Debugger::fireLog() */
    public const DEBUG = \RectorPrefix20220418\Tracy\ILogger::DEBUG, INFO = \RectorPrefix20220418\Tracy\ILogger::INFO, WARNING = \RectorPrefix20220418\Tracy\ILogger::WARNING, ERROR = \RectorPrefix20220418\Tracy\ILogger::ERROR, EXCEPTION = \RectorPrefix20220418\Tracy\ILogger::EXCEPTION, CRITICAL = \RectorPrefix20220418\Tracy\ILogger::CRITICAL;
    /********************* misc ****************d*g**/
    /** @var float timestamp with microseconds of the start of the request */
    public static $time;
    /** @var string URI pattern mask to open editor */
    public static $editor = 'editor://%action/?file=%file&line=%line&search=%search&replace=%replace';
    /** @var array replacements in path */
    public static $editorMapping = [];
    /** @var string command to open browser (use 'start ""' in Windows) */
    public static $browser;
    /** @var string custom static error template */
    public static $errorTemplate;
    /** @var string[] */
    public static $customCssFiles = [];
    /** @var string[] */
    public static $customJsFiles = [];
    /** @var callable[] */
    private static $sourceMappers = [];
    /** @var array|null */
    private static $cpuUsage;
    /********************* services ****************d*g**/
    /** @var BlueScreen */
    private static $blueScreen;
    /** @var Bar */
    private static $bar;
    /** @var ILogger */
    private static $logger;
    /** @var ILogger */
    private static $fireLogger;
    /** @var array{DevelopmentStrategy, ProductionStrategy} */
    private static $strategy;
    /** @var SessionStorage */
    private static $sessionStorage;
    /**
     * Static class - cannot be instantiated.
     */
    public final function __construct()
    {
        throw new \LogicException();
    }
    /**
     * Enables displaying or logging errors and exceptions.
     * @param  bool|string|string[]  $mode  use constant Debugger::PRODUCTION, DEVELOPMENT, DETECT (autodetection) or IP address(es) whitelist.
     * @param  string  $logDirectory  error log directory
     * @param  string|array  $email  administrator email; enables email sending in production mode
     */
    public static function enable($mode = null, ?string $logDirectory = null, $email = null) : void
    {
        if ($mode !== null || self::$productionMode === null) {
            self::$productionMode = \is_bool($mode) ? $mode : !self::detectDebugMode($mode);
        }
        self::$reserved = \str_repeat('t', self::$reservedMemorySize);
        self::$time = $_SERVER['REQUEST_TIME_FLOAT'] ?? \microtime(\true);
        self::$obLevel = \ob_get_level();
        self::$cpuUsage = !self::$productionMode && \function_exists('getrusage') ? \getrusage() : null;
        // logging configuration
        if ($email !== null) {
            self::$email = $email;
        }
        if ($logDirectory !== null) {
            self::$logDirectory = $logDirectory;
        }
        if (self::$logDirectory) {
            if (!\preg_match('#([a-z]+:)?[/\\\\]#Ai', self::$logDirectory)) {
                self::exceptionHandler(new \RuntimeException('Logging directory must be absolute path.'));
                exit(255);
            } elseif (!\is_dir(self::$logDirectory)) {
                self::exceptionHandler(new \RuntimeException("Logging directory '" . self::$logDirectory . "' is not found."));
                exit(255);
            }
        }
        // php configuration
        if (\function_exists('ini_set')) {
            \ini_set('display_errors', '0');
            // or 'stderr'
            \ini_set('html_errors', '0');
            \ini_set('log_errors', '0');
            \ini_set('zend.exception_ignore_args', '0');
        }
        \error_reporting(\E_ALL);
        $strategy = self::getStrategy();
        $strategy->initialize();
        self::dispatch();
        if (self::$enabled) {
            return;
        }
        \register_shutdown_function([self::class, 'shutdownHandler']);
        \set_exception_handler(function (\Throwable $e) {
            self::exceptionHandler($e);
            exit(255);
        });
        \set_error_handler([self::class, 'errorHandler']);
        foreach (['Bar/Bar', 'Bar/DefaultBarPanel', 'BlueScreen/BlueScreen', 'Dumper/Describer', 'Dumper/Dumper', 'Dumper/Exposer', 'Dumper/Renderer', 'Dumper/Value', 'Logger/FireLogger', 'Logger/Logger', 'Session/SessionStorage', 'Session/FileSession', 'Session/NativeSession', 'Helpers'] as $path) {
            require_once \dirname(__DIR__) . "/{$path}.php";
        }
        self::$enabled = \true;
    }
    public static function dispatch() : void
    {
        if (!\RectorPrefix20220418\Tracy\Helpers::isCli() && self::getStrategy()->sendAssets()) {
            self::$showBar = \false;
            exit;
        }
    }
    /**
     * Renders loading <script>
     */
    public static function renderLoader() : void
    {
        self::getStrategy()->renderLoader();
    }
    public static function isEnabled() : bool
    {
        return self::$enabled;
    }
    /**
     * Shutdown handler to catch fatal errors and execute of the planned activities.
     * @internal
     */
    public static function shutdownHandler() : void
    {
        $error = \error_get_last();
        if (\in_array($error['type'] ?? null, [\E_ERROR, \E_CORE_ERROR, \E_COMPILE_ERROR, \E_PARSE, \E_RECOVERABLE_ERROR, \E_USER_ERROR], \true)) {
            self::exceptionHandler(\RectorPrefix20220418\Tracy\Helpers::fixStack(new \ErrorException($error['message'], 0, $error['type'], $error['file'], $error['line'])));
        } elseif (($error['type'] ?? null) === \E_COMPILE_WARNING) {
            \error_clear_last();
            self::errorHandler($error['type'], $error['message'], $error['file'], $error['line']);
        }
        self::$reserved = null;
        if (self::$showBar && !\RectorPrefix20220418\Tracy\Helpers::isCli()) {
            try {
                self::getStrategy()->renderBar();
            } catch (\Throwable $e) {
                self::exceptionHandler($e);
            }
        }
    }
    /**
     * Handler to catch uncaught exception.
     * @internal
     */
    public static function exceptionHandler(\Throwable $exception) : void
    {
        $firstTime = (bool) self::$reserved;
        self::$reserved = null;
        self::$obStatus = \ob_get_status(\true);
        if (!\headers_sent()) {
            \http_response_code(isset($_SERVER['HTTP_USER_AGENT']) && \strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE ') !== \false ? 503 : 500);
        }
        \RectorPrefix20220418\Tracy\Helpers::improveException($exception);
        self::removeOutputBuffers(\true);
        self::getStrategy()->handleException($exception, $firstTime);
        try {
            foreach ($firstTime ? self::$onFatalError : [] as $handler) {
                $handler($exception);
            }
        } catch (\Throwable $e) {
            try {
                self::log($e, self::EXCEPTION);
            } catch (\Throwable $e) {
            }
        }
    }
    /**
     * Handler to catch warnings and notices.
     * @return bool|null   false to call normal error handler, null otherwise
     * @throws ErrorException
     * @internal
     */
    public static function errorHandler(int $severity, string $message, string $file, int $line, ?array $context = null) : bool
    {
        $error = \error_get_last();
        if (($error['type'] ?? null) === \E_COMPILE_WARNING) {
            \error_clear_last();
            self::errorHandler($error['type'], $error['message'], $error['file'], $error['line']);
        }
        if ($context) {
            $context = (array) (object) $context;
            // workaround for PHP bug #80234
        }
        if ($severity === \E_RECOVERABLE_ERROR || $severity === \E_USER_ERROR) {
            if (\RectorPrefix20220418\Tracy\Helpers::findTrace(\debug_backtrace(\DEBUG_BACKTRACE_IGNORE_ARGS), '*::__toString')) {
                // workaround for PHP < 7.4
                $previous = isset($context['e']) && $context['e'] instanceof \Throwable ? $context['e'] : null;
                $e = new \ErrorException($message, 0, $severity, $file, $line, $previous);
                $e->context = $context;
                self::exceptionHandler($e);
                exit(255);
            }
            $e = new \ErrorException($message, 0, $severity, $file, $line);
            $e->context = $context;
            throw $e;
        } elseif ($severity & \error_reporting() || (\is_int(self::$scream) ? $severity & self::$scream : self::$scream)) {
            self::getStrategy()->handleError($severity, $message, $file, $line, $context);
        }
        return \false;
        // calls normal error handler to fill-in error_get_last()
    }
    /** @internal */
    public static function removeOutputBuffers(bool $errorOccurred) : void
    {
        while (\ob_get_level() > self::$obLevel) {
            $status = \ob_get_status();
            if (\in_array($status['name'], ['ob_gzhandler', 'zlib output compression'], \true)) {
                break;
            }
            $fnc = $status['chunk_size'] || !$errorOccurred ? 'ob_end_flush' : 'ob_end_clean';
            if (!@$fnc()) {
                // @ may be not removable
                break;
            }
        }
    }
    /********************* services ****************d*g**/
    public static function getBlueScreen() : \RectorPrefix20220418\Tracy\BlueScreen
    {
        if (!self::$blueScreen) {
            self::$blueScreen = new \RectorPrefix20220418\Tracy\BlueScreen();
            self::$blueScreen->info = ['PHP ' . \PHP_VERSION, $_SERVER['SERVER_SOFTWARE'] ?? null, 'Tracy ' . self::VERSION];
        }
        return self::$blueScreen;
    }
    public static function getBar() : \RectorPrefix20220418\Tracy\Bar
    {
        if (!self::$bar) {
            self::$bar = new \RectorPrefix20220418\Tracy\Bar();
            self::$bar->addPanel($info = new \RectorPrefix20220418\Tracy\DefaultBarPanel('info'), 'Tracy:info');
            $info->cpuUsage = self::$cpuUsage;
            self::$bar->addPanel(new \RectorPrefix20220418\Tracy\DefaultBarPanel('errors'), 'Tracy:errors');
            // filled by errorHandler()
        }
        return self::$bar;
    }
    public static function setLogger(\RectorPrefix20220418\Tracy\ILogger $logger) : void
    {
        self::$logger = $logger;
    }
    public static function getLogger() : \RectorPrefix20220418\Tracy\ILogger
    {
        if (!self::$logger) {
            self::$logger = new \RectorPrefix20220418\Tracy\Logger(self::$logDirectory, self::$email, self::getBlueScreen());
            self::$logger->directory =& self::$logDirectory;
            // back compatiblity
            self::$logger->email =& self::$email;
        }
        return self::$logger;
    }
    public static function getFireLogger() : \RectorPrefix20220418\Tracy\ILogger
    {
        if (!self::$fireLogger) {
            self::$fireLogger = new \RectorPrefix20220418\Tracy\FireLogger();
        }
        return self::$fireLogger;
    }
    /** @return ProductionStrategy|DevelopmentStrategy @internal */
    public static function getStrategy()
    {
        if (empty(self::$strategy[self::$productionMode])) {
            self::$strategy[self::$productionMode] = self::$productionMode ? new \RectorPrefix20220418\Tracy\ProductionStrategy() : new \RectorPrefix20220418\Tracy\DevelopmentStrategy(self::getBar(), self::getBlueScreen(), new \RectorPrefix20220418\Tracy\DeferredContent(self::getSessionStorage()));
        }
        return self::$strategy[self::$productionMode];
    }
    public static function setSessionStorage(\RectorPrefix20220418\Tracy\SessionStorage $storage) : void
    {
        if (self::$sessionStorage) {
            throw new \Exception('Storage is already set.');
        }
        self::$sessionStorage = $storage;
    }
    /** @internal */
    public static function getSessionStorage() : \RectorPrefix20220418\Tracy\SessionStorage
    {
        if (!self::$sessionStorage) {
            self::$sessionStorage = @\is_dir($dir = \session_save_path()) || @\is_dir($dir = \ini_get('upload_tmp_dir')) || @\is_dir($dir = \sys_get_temp_dir()) || ($dir = self::$logDirectory) ? new \RectorPrefix20220418\Tracy\FileSession($dir) : new \RectorPrefix20220418\Tracy\NativeSession();
        }
        return self::$sessionStorage;
    }
    /********************* useful tools ****************d*g**/
    /**
     * Dumps information about a variable in readable format.
     * @tracySkipLocation
     * @param  mixed  $var  variable to dump
     * @param  bool   $return  return output instead of printing it? (bypasses $productionMode)
     * @return mixed  variable itself or dump
     */
    public static function dump($var, bool $return = \false)
    {
        if ($return) {
            $options = [\RectorPrefix20220418\Tracy\Dumper::DEPTH => self::$maxDepth, \RectorPrefix20220418\Tracy\Dumper::TRUNCATE => self::$maxLength, \RectorPrefix20220418\Tracy\Dumper::ITEMS => self::$maxItems];
            return \RectorPrefix20220418\Tracy\Helpers::isCli() ? \RectorPrefix20220418\Tracy\Dumper::toText($var) : \RectorPrefix20220418\Tracy\Helpers::capture(function () use($var, $options) {
                \RectorPrefix20220418\Tracy\Dumper::dump($var, $options);
            });
        } elseif (!self::$productionMode) {
            $html = \RectorPrefix20220418\Tracy\Helpers::isHtmlMode();
            echo $html ? '<tracy-div>' : '';
            \RectorPrefix20220418\Tracy\Dumper::dump($var, [\RectorPrefix20220418\Tracy\Dumper::DEPTH => self::$maxDepth, \RectorPrefix20220418\Tracy\Dumper::TRUNCATE => self::$maxLength, \RectorPrefix20220418\Tracy\Dumper::ITEMS => self::$maxItems, \RectorPrefix20220418\Tracy\Dumper::LOCATION => self::$showLocation, \RectorPrefix20220418\Tracy\Dumper::THEME => self::$dumpTheme, \RectorPrefix20220418\Tracy\Dumper::KEYS_TO_HIDE => self::$keysToHide]);
            echo $html ? '</tracy-div>' : '';
        }
        return $var;
    }
    /**
     * Starts/stops stopwatch.
     * @return float   elapsed seconds
     */
    public static function timer(?string $name = null) : float
    {
        static $time = [];
        $now = \microtime(\true);
        $delta = isset($time[$name]) ? $now - $time[$name] : 0;
        $time[$name] = $now;
        return $delta;
    }
    /**
     * Dumps information about a variable in Tracy Debug Bar.
     * @tracySkipLocation
     * @param  mixed  $var
     * @return mixed  variable itself
     */
    public static function barDump($var, ?string $title = null, array $options = [])
    {
        if (!self::$productionMode) {
            static $panel;
            if (!$panel) {
                self::getBar()->addPanel($panel = new \RectorPrefix20220418\Tracy\DefaultBarPanel('dumps'), 'Tracy:dumps');
            }
            $panel->data[] = ['title' => $title, 'dump' => \RectorPrefix20220418\Tracy\Dumper::toHtml($var, $options + [\RectorPrefix20220418\Tracy\Dumper::DEPTH => self::$maxDepth, \RectorPrefix20220418\Tracy\Dumper::TRUNCATE => self::$maxLength, \RectorPrefix20220418\Tracy\Dumper::LOCATION => self::$showLocation ?: \RectorPrefix20220418\Tracy\Dumper::LOCATION_CLASS | \RectorPrefix20220418\Tracy\Dumper::LOCATION_SOURCE, \RectorPrefix20220418\Tracy\Dumper::LAZY => \true])];
        }
        return $var;
    }
    /**
     * Logs message or exception.
     * @param  mixed  $message
     * @return mixed
     */
    public static function log($message, string $level = \RectorPrefix20220418\Tracy\ILogger::INFO)
    {
        return self::getLogger()->log($message, $level);
    }
    /**
     * Sends message to FireLogger console.
     * @param  mixed  $message
     */
    public static function fireLog($message) : bool
    {
        return !self::$productionMode && self::$showFireLogger ? self::getFireLogger()->log($message) : \false;
    }
    /** @internal */
    public static function addSourceMapper(callable $mapper) : void
    {
        self::$sourceMappers[] = $mapper;
    }
    /** @return array{file: string, line: int, label: string, active: bool} */
    public static function mapSource(string $file, int $line) : ?array
    {
        foreach (self::$sourceMappers as $mapper) {
            if ($res = $mapper($file, $line)) {
                return $res;
            }
        }
        return null;
    }
    /**
     * Detects debug mode by IP address.
     * @param  string|array  $list  IP addresses or computer names whitelist detection
     */
    public static function detectDebugMode($list = null) : bool
    {
        $addr = $_SERVER['REMOTE_ADDR'] ?? \php_uname('n');
        $secret = isset($_COOKIE[self::COOKIE_SECRET]) && \is_string($_COOKIE[self::COOKIE_SECRET]) ? $_COOKIE[self::COOKIE_SECRET] : null;
        $list = \is_string($list) ? \preg_split('#[,\\s]+#', $list) : (array) $list;
        if (!isset($_SERVER['HTTP_X_FORWARDED_FOR']) && !isset($_SERVER['HTTP_FORWARDED'])) {
            $list[] = '127.0.0.1';
            $list[] = '::1';
            $list[] = '[::1]';
            // workaround for PHP < 7.3.4
        }
        return \in_array($addr, $list, \true) || \in_array("{$secret}@{$addr}", $list, \true);
    }
}
