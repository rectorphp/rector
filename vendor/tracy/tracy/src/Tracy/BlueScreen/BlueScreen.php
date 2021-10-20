<?php

/**
 * This file is part of the Tracy (https://tracy.nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */
declare (strict_types=1);
namespace RectorPrefix20211020\Tracy;

/**
 * Red BlueScreen.
 */
class BlueScreen
{
    private const MAX_MESSAGE_LENGTH = 2000;
    /** @var string[] */
    public $info = [];
    /** @var string[] paths to be collapsed in stack trace (e.g. core libraries) */
    public $collapsePaths = [];
    /** @var int  */
    public $maxDepth = 5;
    /** @var int  */
    public $maxLength = 150;
    /** @var callable|null  a callable returning true for sensitive data; fn(string $key, mixed $val): bool */
    public $scrubber;
    /** @var string[] */
    public $keysToHide = ['password', 'passwd', 'pass', 'pwd', 'creditcard', 'credit card', 'cc', 'pin', self::class . '::$snapshot'];
    /** @var bool */
    public $showEnvironment = \true;
    /** @var callable[] */
    private $panels = [];
    /** @var callable[] functions that returns action for exceptions */
    private $actions = [];
    /** @var array */
    private $snapshot;
    public function __construct()
    {
        $this->collapsePaths = \preg_match('#(.+/vendor)/tracy/tracy/src/Tracy/BlueScreen$#', \strtr(__DIR__, '\\', '/'), $m) ? [$m[1] . '/tracy', $m[1] . '/nette', $m[1] . '/latte'] : [\dirname(__DIR__)];
    }
    /**
     * Add custom panel as function (?\Throwable $e): ?array
     * @return static
     * @param callable $panel
     */
    public function addPanel($panel) : self
    {
        if (!\in_array($panel, $this->panels, \true)) {
            $this->panels[] = $panel;
        }
        return $this;
    }
    /**
     * Add action.
     * @return static
     * @param callable $action
     */
    public function addAction($action) : self
    {
        $this->actions[] = $action;
        return $this;
    }
    /**
     * Renders blue screen.
     * @param \Throwable $exception
     */
    public function render($exception) : void
    {
        if (\RectorPrefix20211020\Tracy\Helpers::isAjax() && \session_status() === \PHP_SESSION_ACTIVE) {
            $_SESSION['_tracy']['bluescreen'][$_SERVER['HTTP_X_TRACY_AJAX']] = ['content' => \RectorPrefix20211020\Tracy\Helpers::capture(function () use($exception) {
                $this->renderTemplate($exception, __DIR__ . '/assets/content.phtml');
            }), 'time' => \time()];
        } else {
            if (!\headers_sent()) {
                \header('Content-Type: text/html; charset=UTF-8');
            }
            $this->renderTemplate($exception, __DIR__ . '/assets/page.phtml');
        }
    }
    /**
     * Renders blue screen to file (if file exists, it will not be overwritten).
     * @param \Throwable $exception
     * @param string $file
     */
    public function renderToFile($exception, $file) : bool
    {
        if ($handle = @\fopen($file, 'x')) {
            \ob_start();
            // double buffer prevents sending HTTP headers in some PHP
            \ob_start(function ($buffer) use($handle) : void {
                \fwrite($handle, $buffer);
            }, 4096);
            $this->renderTemplate($exception, __DIR__ . '/assets/page.phtml', \false);
            \ob_end_flush();
            \ob_end_clean();
            \fclose($handle);
            return \true;
        }
        return \false;
    }
    private function renderTemplate(\Throwable $exception, string $template, $toScreen = \true) : void
    {
        $showEnvironment = $this->showEnvironment && \strpos($exception->getMessage(), 'Allowed memory size') === \false;
        $info = \array_filter($this->info);
        $source = \RectorPrefix20211020\Tracy\Helpers::getSource();
        $title = $exception instanceof \ErrorException ? \RectorPrefix20211020\Tracy\Helpers::errorTypeToString($exception->getSeverity()) : \RectorPrefix20211020\Tracy\Helpers::getClass($exception);
        $lastError = $exception instanceof \ErrorException || $exception instanceof \Error ? null : \error_get_last();
        if (\function_exists('apache_request_headers')) {
            $httpHeaders = \apache_request_headers();
        } else {
            $httpHeaders = \array_filter($_SERVER, function ($k) {
                return \strncmp($k, 'HTTP_', 5) === 0;
            }, \ARRAY_FILTER_USE_KEY);
            $httpHeaders = \array_combine(\array_map(function ($k) {
                return \strtolower(\strtr(\substr($k, 5), '_', '-'));
            }, \array_keys($httpHeaders)), $httpHeaders);
        }
        $snapshot =& $this->snapshot;
        $snapshot = [];
        $dump = $this->getDumper();
        $css = \array_map('file_get_contents', \array_merge([__DIR__ . '/assets/bluescreen.css', __DIR__ . '/../Toggle/toggle.css', __DIR__ . '/../TableSort/table-sort.css', __DIR__ . '/../Dumper/assets/dumper-light.css'], \RectorPrefix20211020\Tracy\Debugger::$customCssFiles));
        $css = \RectorPrefix20211020\Tracy\Helpers::minifyCss(\implode($css));
        $nonce = $toScreen ? \RectorPrefix20211020\Tracy\Helpers::getNonce() : null;
        $actions = $toScreen ? $this->renderActions($exception) : [];
        require $template;
    }
    /**
     * @return \stdClass[]
     */
    private function renderPanels(?\Throwable $ex) : array
    {
        $obLevel = \ob_get_level();
        $res = [];
        foreach ($this->panels as $callback) {
            try {
                $panel = $callback($ex);
                if (empty($panel['tab']) || empty($panel['panel'])) {
                    continue;
                }
                $res[] = (object) $panel;
                continue;
            } catch (\Throwable $e) {
            }
            while (\ob_get_level() > $obLevel) {
                // restore ob-level if broken
                \ob_end_clean();
            }
            \is_callable($callback, \true, $name);
            $res[] = (object) ['tab' => "Error in panel {$name}", 'panel' => \nl2br(\RectorPrefix20211020\Tracy\Helpers::escapeHtml($e))];
        }
        return $res;
    }
    /**
     * @return array[]
     */
    private function renderActions(\Throwable $ex) : array
    {
        $actions = [];
        foreach ($this->actions as $callback) {
            $action = $callback($ex);
            if (!empty($action['link']) && !empty($action['label'])) {
                $actions[] = $action;
            }
        }
        if (\property_exists($ex, 'tracyAction') && !empty($ex->tracyAction['link']) && !empty($ex->tracyAction['label'])) {
            $actions[] = $ex->tracyAction;
        }
        if (\preg_match('# ([\'"])(\\w{3,}(?:\\\\\\w{3,})+)\\1#i', $ex->getMessage(), $m)) {
            $class = $m[2];
            if (!\class_exists($class, \false) && !\interface_exists($class, \false) && !\trait_exists($class, \false) && ($file = \RectorPrefix20211020\Tracy\Helpers::guessClassFile($class)) && !\is_file($file)) {
                $actions[] = ['link' => \RectorPrefix20211020\Tracy\Helpers::editorUri($file, 1, 'create'), 'label' => 'create class'];
            }
        }
        if (\preg_match('# ([\'"])((?:/|[a-z]:[/\\\\])\\w[^\'"]+\\.\\w{2,5})\\1#i', $ex->getMessage(), $m)) {
            $file = $m[2];
            $actions[] = ['link' => \RectorPrefix20211020\Tracy\Helpers::editorUri($file, 1, $label = \is_file($file) ? 'open' : 'create'), 'label' => $label . ' file'];
        }
        $query = ($ex instanceof \ErrorException ? '' : \RectorPrefix20211020\Tracy\Helpers::getClass($ex) . ' ') . \preg_replace('#\'.*\'|".*"#Us', '', $ex->getMessage());
        $actions[] = ['link' => 'https://www.google.com/search?sourceid=tracy&q=' . \urlencode($query), 'label' => 'search', 'external' => \true];
        if ($ex instanceof \ErrorException && !empty($ex->skippable) && \preg_match('#^https?://#', $source = \RectorPrefix20211020\Tracy\Helpers::getSource())) {
            $actions[] = ['link' => $source . (\strpos($source, '?') ? '&' : '?') . '_tracy_skip_error', 'label' => 'skip error'];
        }
        return $actions;
    }
    /**
     * Returns syntax highlighted source code.
     * @param string $file
     * @param int $line
     * @param int $lines
     */
    public static function highlightFile($file, $line, $lines = 15) : ?string
    {
        $source = @\file_get_contents($file);
        // @ file may not exist
        if ($source === \false) {
            return null;
        }
        $source = static::highlightPhp($source, $line, $lines);
        if ($editor = \RectorPrefix20211020\Tracy\Helpers::editorUri($file, $line)) {
            $source = \substr_replace($source, ' title="Ctrl-Click to open in editor" data-tracy-href="' . \RectorPrefix20211020\Tracy\Helpers::escapeHtml($editor) . '"', 4, 0);
        }
        return $source;
    }
    /**
     * Returns syntax highlighted source code.
     * @param string $source
     * @param int $line
     * @param int $lines
     */
    public static function highlightPhp($source, $line, $lines = 15) : string
    {
        if (\function_exists('ini_set')) {
            \ini_set('highlight.comment', '#998; font-style: italic');
            \ini_set('highlight.default', '#000');
            \ini_set('highlight.html', '#06B');
            \ini_set('highlight.keyword', '#D24; font-weight: bold');
            \ini_set('highlight.string', '#080');
        }
        $source = \preg_replace('#(__halt_compiler\\s*\\(\\)\\s*;).*#is', '$1', $source);
        $source = \str_replace(["\r\n", "\r"], "\n", $source);
        $source = \explode("\n", \highlight_string($source, \true));
        $out = $source[0];
        // <code><span color=highlight.html>
        $source = \str_replace('<br />', "\n", $source[1]);
        $out .= static::highlightLine($source, $line, $lines);
        $out = \str_replace('&nbsp;', ' ', $out);
        return "<pre class='code'><div>{$out}</div></pre>";
    }
    /**
     * Returns highlighted line in HTML code.
     * @param string $html
     * @param int $line
     * @param int $lines
     */
    public static function highlightLine($html, $line, $lines = 15) : string
    {
        $source = \explode("\n", "\n" . \str_replace("\r\n", "\n", $html));
        $out = '';
        $spans = 1;
        $start = $i = \max(1, \min($line, \count($source) - 1) - (int) \floor($lines * 2 / 3));
        while (--$i >= 1) {
            // find last highlighted block
            if (\preg_match('#.*(</?span[^>]*>)#', $source[$i], $m)) {
                if ($m[1] !== '</span>') {
                    $spans++;
                    $out .= $m[1];
                }
                break;
            }
        }
        $source = \array_slice($source, $start, $lines, \true);
        \end($source);
        $numWidth = \strlen((string) \key($source));
        foreach ($source as $n => $s) {
            $spans += \substr_count($s, '<span') - \substr_count($s, '</span');
            $s = \str_replace(["\r", "\n"], ['', ''], $s);
            \preg_match_all('#<[^>]+>#', $s, $tags);
            if ($n == $line) {
                $out .= \sprintf("<span class='highlight'>%{$numWidth}s:    %s\n</span>%s", $n, \strip_tags($s), \implode('', $tags[0]));
            } else {
                $out .= \sprintf("<span class='line'>%{$numWidth}s:</span>    %s\n", $n, $s);
            }
        }
        $out .= \str_repeat('</span>', $spans) . '</code>';
        return $out;
    }
    /**
     * Returns syntax highlighted source code to Terminal.
     * @param string $file
     * @param int $line
     * @param int $lines
     */
    public static function highlightPhpCli($file, $line, $lines = 15) : string
    {
        $source = @\file_get_contents($file);
        // @ file may not exist
        if ($source === \false) {
            return null;
        }
        $s = self::highlightPhp($source, $line, $lines);
        $colors = ['color: ' . \ini_get('highlight.comment') => '1;30', 'color: ' . \ini_get('highlight.default') => '1;36', 'color: ' . \ini_get('highlight.html') => '1;35', 'color: ' . \ini_get('highlight.keyword') => '1;37', 'color: ' . \ini_get('highlight.string') => '1;32', 'line' => '1;30', 'highlight' => "1;37m\33[41"];
        $stack = ['0'];
        $s = \preg_replace_callback('#<\\w+(?: (class|style)=["\'](.*?)["\'])?[^>]*>|</\\w+>#', function ($m) use($colors, &$stack) : string {
            if ($m[0][1] === '/') {
                \array_pop($stack);
            } else {
                $stack[] = isset($m[2], $colors[$m[2]]) ? $colors[$m[2]] : '0';
            }
            return "\33[0m\33[" . \end($stack) . 'm';
        }, $s);
        $s = \htmlspecialchars_decode(\strip_tags($s), \ENT_QUOTES | \ENT_HTML5);
        return $s;
    }
    /**
     * Should a file be collapsed in stack trace?
     * @internal
     * @param string $file
     */
    public function isCollapsed($file) : bool
    {
        $file = \strtr($file, '\\', '/') . '/';
        foreach ($this->collapsePaths as $path) {
            $path = \strtr($path, '\\', '/') . '/';
            if (\strncmp($file, $path, \strlen($path)) === 0) {
                return \true;
            }
        }
        return \false;
    }
    /** @internal */
    public function getDumper() : \Closure
    {
        return function ($v, $k = null) : string {
            return \RectorPrefix20211020\Tracy\Dumper::toHtml($v, [\RectorPrefix20211020\Tracy\Dumper::DEPTH => $this->maxDepth, \RectorPrefix20211020\Tracy\Dumper::TRUNCATE => $this->maxLength, \RectorPrefix20211020\Tracy\Dumper::SNAPSHOT => &$this->snapshot, \RectorPrefix20211020\Tracy\Dumper::LOCATION => \RectorPrefix20211020\Tracy\Dumper::LOCATION_CLASS, \RectorPrefix20211020\Tracy\Dumper::SCRUBBER => $this->scrubber, \RectorPrefix20211020\Tracy\Dumper::KEYS_TO_HIDE => $this->keysToHide], $k);
        };
    }
    /**
     * @param \Throwable $exception
     */
    public function formatMessage($exception) : string
    {
        $msg = \RectorPrefix20211020\Tracy\Helpers::encodeString(\trim((string) $exception->getMessage()), self::MAX_MESSAGE_LENGTH, \false);
        // highlight 'string'
        $msg = \preg_replace('#\'\\S(?:[^\']|\\\\\')*\\S\'|"\\S(?:[^"]|\\\\")*\\S"#', '<i>$0</i>', $msg);
        // clickable class & methods
        $msg = \preg_replace_callback('#(\\w+\\\\[\\w\\\\]+\\w)(?:::(\\w+))?#', function ($m) {
            if (isset($m[2]) && \method_exists($m[1], $m[2])) {
                $r = new \ReflectionMethod($m[1], $m[2]);
            } elseif (\class_exists($m[1], \false) || \interface_exists($m[1], \false)) {
                $r = new \ReflectionClass($m[1]);
            }
            if (empty($r) || !$r->getFileName()) {
                return $m[0];
            }
            return '<a href="' . \RectorPrefix20211020\Tracy\Helpers::escapeHtml(\RectorPrefix20211020\Tracy\Helpers::editorUri($r->getFileName(), $r->getStartLine())) . '" class="tracy-editor">' . $m[0] . '</a>';
        }, $msg);
        // clickable file name
        $msg = \preg_replace_callback('#([\\w\\\\/.:-]+\\.(?:php|phpt|phtml|latte|neon))(?|:(\\d+)| on line (\\d+))?#', function ($m) {
            return @\is_file($m[1]) ? '<a href="' . \RectorPrefix20211020\Tracy\Helpers::escapeHtml(\RectorPrefix20211020\Tracy\Helpers::editorUri($m[1], isset($m[2]) ? (int) $m[2] : null)) . '" class="tracy-editor">' . $m[0] . '</a>' : $m[0];
        }, $msg);
        return $msg;
    }
    private function renderPhpInfo() : void
    {
        \ob_start();
        @\phpinfo(\INFO_LICENSE);
        // @ phpinfo may be disabled
        $license = \ob_get_clean();
        \ob_start();
        @\phpinfo(\INFO_CONFIGURATION | \INFO_MODULES);
        // @ phpinfo may be disabled
        $info = \ob_get_clean();
        if (\strpos($license, '<body') === \false) {
            echo '<pre class="tracy-dump tracy-light">', \RectorPrefix20211020\Tracy\Helpers::escapeHtml($info), '</pre>';
        } else {
            $info = \str_replace('<table', '<table class="tracy-sortable"', $info);
            echo \preg_replace('#^.+<body>|</body>.+\\z#s', '', $info);
        }
    }
}
