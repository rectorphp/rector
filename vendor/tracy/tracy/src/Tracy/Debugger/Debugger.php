<?php

/**
 * This file is part of the Tracy (https://tracy.nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */
declare (strict_types=1);
namespace RectorPrefix202304\Tracy;

use ErrorException;
/**
 * Debugger: displays and logs errors.
 */
class Debugger
{
    public const VERSION = '2.10.2';
    /** server modes for Debugger::enable() */
    public const Development = \false, Production = \true, Detect = null;
    public const DEVELOPMENT = self::Development, PRODUCTION = self::Production, DETECT = self::Detect;
    public const CookieSecret = 'tracy-debug';
    public const COOKIE_SECRET = self::CookieSecret;
    /** in production mode is suppressed any debugging output
     * @var bool|null */
    public static $productionMode = self::DETECT;
    /** whether to display debug bar in development mode
     * @var bool */
    public static $showBar = \true;
    /** size of reserved memory
     * @var int */
    public static $reservedMemorySize = 500000;
    /**
     * @var bool
     */
    private static $enabled = \false;
    /** reserved memory; also prevents double rendering
     * @var string|null */
    private static $reserved;
    /** initial output buffer level
     * @var int */
    private static $obLevel;
    /** output buffer status @internal
     * @var mixed[]|null */
    public static $obStatus;
    /********************* errors and exceptions reporting ****************d*g**/
    /** determines whether any error will cause immediate death in development mode; if integer that it's matched against error severity
     * @var bool|int */
    public static $strictMode = \false;
    /** disables the @ (shut-up) operator so that notices and warnings are no longer hidden; if integer than it's matched against error severity
     * @var bool|int */
    public static $scream = \false;
    /** @var callable[] functions that are automatically called after fatal error */
    public static $onFatalError = [];
    /********************* Debugger::dump() ****************d*g**/
    /** how many nested levels of array/object properties display by dump()
     * @var int */
    public static $maxDepth = 15;
    /** how long strings display by dump()
     * @var int */
    public static $maxLength = 150;
    /** how many items in array/object display by dump()
     * @var int */
    public static $maxItems = 100;
    /** display location by dump()?
     * @var bool|null */
    public static $showLocation;
    /** @var string[] sensitive keys not displayed by dump() */
    public static $keysToHide = [];
    /** theme for dump()
     * @var string */
    public static $dumpTheme = 'light';
    /** @deprecated */
    public static $maxLen;
    /********************* logging ****************d*g**/
    /** name of the directory where errors should be logged
     * @var string|null */
    public static $logDirectory;
    /** log bluescreen in production mode for this error severity
     * @var int */
    public static $logSeverity = 0;
    /** email(s) to which send error notifications
     * @var string|mixed[]|null */
    public static $email = null;
    /** for Debugger::log() */
    public const DEBUG = ILogger::DEBUG, INFO = ILogger::INFO, WARNING = ILogger::WARNING, ERROR = ILogger::ERROR, EXCEPTION = ILogger::EXCEPTION, CRITICAL = ILogger::CRITICAL;
    /********************* misc ****************d*g**/
    /** timestamp with microseconds of the start of the request
     * @var float */
    public static $time;
    /** URI pattern mask to open editor
     * @var string|null */
    public static $editor = 'editor://%action/?file=%file&line=%line&search=%search&replace=%replace';
    /** replacements in path
     * @var mixed[] */
    public static $editorMapping = [];
    /** command to open browser (use 'start ""' in Windows)
     * @var string|null */
    public static $browser;
    /** custom static error template
     * @var string|null */
    public static $errorTemplate;
    /** @var string[] */
    public static $customCssFiles = [];
    /** @var string[] */
    public static $customJsFiles = [];
    /** @var callable[] */
    private static $sourceMappers = [];
    /**
     * @var mixed[]|null
     */
    private static $cpuUsage;
    /********************* services ****************d*g**/
    /**
     * @var \Tracy\BlueScreen
     */
    private static $blueScreen;
    /**
     * @var \Tracy\Bar
     */
    private static $bar;
    /**
     * @var \Tracy\ILogger
     */
    private static $logger;
    /** @var array{DevelopmentStrategy, ProductionStrategy} */
    private static $strategy;
    /**
     * @var \Tracy\SessionStorage
     */
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
     * @param  bool|string|string[]  $mode  use constant Debugger::Production, Development, Detect (autodetection) or IP address(es) whitelist.
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
        foreach (['Bar/Bar', 'Bar/DefaultBarPanel', 'BlueScreen/BlueScreen', 'Dumper/Describer', 'Dumper/Dumper', 'Dumper/Exposer', 'Dumper/Renderer', 'Dumper/Value', 'Logger/Logger', 'Session/SessionStorage', 'Session/FileSession', 'Session/NativeSession', 'Helpers'] as $path) {
            require_once \dirname(__DIR__) . "/{$path}.php";
        }
        self::$enabled = \true;
    }
    public static function dispatch() : void
    {
        if (!Helpers::isCli() && self::getStrategy()->sendAssets()) {
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
            self::exceptionHandler(Helpers::fixStack(new ErrorException($error['message'], 0, $error['type'], $error['file'], $error['line'])));
        } elseif (($error['type'] ?? null) === \E_COMPILE_WARNING) {
            \error_clear_last();
            self::errorHandler($error['type'], $error['message'], $error['file'], $error['line']);
        }
        self::$reserved = null;
        if (self::$showBar && !Helpers::isCli()) {
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
        Helpers::improveException($exception);
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
    public static function errorHandler(int $severity, string $message, string $file, int $line) : bool
    {
        $error = \error_get_last();
        if (($error['type'] ?? null) === \E_COMPILE_WARNING) {
            \error_clear_last();
            self::errorHandler($error['type'], $error['message'], $error['file'], $error['line']);
        }
        if ($severity === \E_RECOVERABLE_ERROR || $severity === \E_USER_ERROR) {
            throw new ErrorException($message, 0, $severity, $file, $line);
        } elseif ($severity & \error_reporting() || (\is_int(self::$scream) ? $severity & self::$scream : self::$scream)) {
            self::getStrategy()->handleError($severity, $message, $file, $line);
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
    public static function getBlueScreen() : BlueScreen
    {
        if (empty(self::$blueScreen)) {
            self::$blueScreen = new BlueScreen();
            self::$blueScreen->info = ['PHP ' . \PHP_VERSION, $_SERVER['SERVER_SOFTWARE'] ?? null, 'Tracy ' . self::VERSION];
        }
        return self::$blueScreen;
    }
    public static function getBar() : Bar
    {
        if (empty(self::$bar)) {
            self::$bar = new Bar();
            self::$bar->addPanel($info = new DefaultBarPanel('info'), 'Tracy:info');
            $info->cpuUsage = self::$cpuUsage;
            self::$bar->addPanel(new DefaultBarPanel('errors'), 'Tracy:errors');
            // filled by errorHandler()
        }
        return self::$bar;
    }
    public static function setLogger(ILogger $logger) : void
    {
        self::$logger = $logger;
    }
    public static function getLogger() : ILogger
    {
        if (empty(self::$logger)) {
            self::$logger = new Logger(self::$logDirectory, self::$email, self::getBlueScreen());
            self::$logger->directory =& self::$logDirectory;
            // back compatiblity
            self::$logger->email =& self::$email;
        }
        return self::$logger;
    }
    /** @internal
     * @return \Tracy\ProductionStrategy|\Tracy\DevelopmentStrategy */
    public static function getStrategy()
    {
        if (empty(self::$strategy[self::$productionMode])) {
            self::$strategy[self::$productionMode] = self::$productionMode ? new ProductionStrategy() : new DevelopmentStrategy(self::getBar(), self::getBlueScreen(), new DeferredContent(self::getSessionStorage()));
        }
        return self::$strategy[self::$productionMode];
    }
    public static function setSessionStorage(SessionStorage $storage) : void
    {
        if (isset(self::$sessionStorage)) {
            throw new \Exception('Storage is already set.');
        }
        self::$sessionStorage = $storage;
    }
    /** @internal */
    public static function getSessionStorage() : SessionStorage
    {
        if (empty(self::$sessionStorage)) {
            self::$sessionStorage = @\is_dir($dir = \session_save_path()) || @\is_dir($dir = \ini_get('upload_tmp_dir')) || @\is_dir($dir = \sys_get_temp_dir()) || ($dir = self::$logDirectory) ? new FileSession($dir) : new NativeSession();
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
            $options = [Dumper::DEPTH => self::$maxDepth, Dumper::TRUNCATE => self::$maxLength, Dumper::ITEMS => self::$maxItems];
            return Helpers::isCli() ? Dumper::toText($var) : Helpers::capture(function () use($var, $options) {
                return Dumper::dump($var, $options);
            });
        } elseif (!self::$productionMode) {
            $html = Helpers::isHtmlMode();
            echo $html ? '<tracy-div>' : '';
            Dumper::dump($var, [Dumper::DEPTH => self::$maxDepth, Dumper::TRUNCATE => self::$maxLength, Dumper::ITEMS => self::$maxItems, Dumper::LOCATION => self::$showLocation, Dumper::THEME => self::$dumpTheme, Dumper::KEYS_TO_HIDE => self::$keysToHide]);
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
        $now = \hrtime(\true);
        $delta = isset($time[$name]) ? $now - $time[$name] : 0;
        $time[$name] = $now;
        return $delta / 1000000000.0;
    }
    /**
     * Dumps information about a variable in Tracy Debug Bar.
     * @tracySkipLocation
     * @return mixed  variable itself
     * @param mixed $var
     */
    public static function barDump($var, ?string $title = null, array $options = [])
    {
        if (!self::$productionMode) {
            static $panel;
            if (!$panel) {
                self::getBar()->addPanel($panel = new DefaultBarPanel('dumps'), 'Tracy:dumps');
            }
            $panel->data[] = ['title' => $title, 'dump' => Dumper::toHtml($var, $options + [Dumper::DEPTH => self::$maxDepth, Dumper::TRUNCATE => self::$maxLength, Dumper::LOCATION => self::$showLocation ?: Dumper::LOCATION_CLASS | Dumper::LOCATION_SOURCE, Dumper::LAZY => \true])];
        }
        return $var;
    }
    /**
     * Logs message or exception.
     * @param mixed $message
     * @return mixed
     */
    public static function log($message, string $level = ILogger::INFO)
    {
        return self::getLogger()->log($message, $level);
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
        $secret = isset($_COOKIE[self::CookieSecret]) && \is_string($_COOKIE[self::CookieSecret]) ? $_COOKIE[self::CookieSecret] : null;
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
