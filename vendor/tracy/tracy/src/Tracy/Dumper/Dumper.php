<?php

/**
 * This file is part of the Tracy (https://tracy.nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */
declare (strict_types=1);
namespace RectorPrefix202304\Tracy;

use RectorPrefix202304\Ds;
use RectorPrefix202304\Tracy\Dumper\Describer;
use RectorPrefix202304\Tracy\Dumper\Exposer;
use RectorPrefix202304\Tracy\Dumper\Renderer;
/**
 * Dumps a variable.
 */
class Dumper
{
    public const DEPTH = 'depth', TRUNCATE = 'truncate', ITEMS = 'items', COLLAPSE = 'collapse', COLLAPSE_COUNT = 'collapsecount', LOCATION = 'location', OBJECT_EXPORTERS = 'exporters', LAZY = 'lazy', LIVE = 'live', SNAPSHOT = 'snapshot', DEBUGINFO = 'debuginfo', KEYS_TO_HIDE = 'keystohide', SCRUBBER = 'scrubber', THEME = 'theme', HASH = 'hash';
    // show object and reference hashes (defaults to true)
    public const LOCATION_CLASS = 0b1, LOCATION_SOURCE = 0b11, LOCATION_LINK = self::LOCATION_SOURCE;
    // deprecated
    public const HIDDEN_VALUE = Describer::HiddenValue;
    /** @var Dumper\Value[] */
    public static $liveSnapshot = [];
    /**
     * @var mixed[]|null
     */
    public static $terminalColors = ['bool' => '1;33', 'null' => '1;33', 'number' => '1;32', 'string' => '1;36', 'array' => '1;31', 'public' => '1;37', 'protected' => '1;37', 'private' => '1;37', 'dynamic' => '1;37', 'virtual' => '1;37', 'object' => '1;31', 'resource' => '1;37', 'indent' => '1;30'];
    /**
     * @var mixed[]
     */
    public static $resources = ['stream' => 'stream_get_meta_data', 'stream-context' => 'stream_context_get_options', 'curl' => 'curl_getinfo'];
    /**
     * @var mixed[]
     */
    public static $objectExporters = [\Closure::class => [Exposer::class, 'exposeClosure'], \UnitEnum::class => [Exposer::class, 'exposeEnum'], \ArrayObject::class => [Exposer::class, 'exposeArrayObject'], \SplFileInfo::class => [Exposer::class, 'exposeSplFileInfo'], \SplObjectStorage::class => [Exposer::class, 'exposeSplObjectStorage'], \__PHP_Incomplete_Class::class => [Exposer::class, 'exposePhpIncompleteClass'], \Generator::class => [Exposer::class, 'exposeGenerator'], \Fiber::class => [Exposer::class, 'exposeFiber'], \DOMNode::class => [Exposer::class, 'exposeDOMNode'], \DOMNodeList::class => [Exposer::class, 'exposeDOMNodeList'], \DOMNamedNodeMap::class => [Exposer::class, 'exposeDOMNodeList'], Ds\Collection::class => [Exposer::class, 'exposeDsCollection'], Ds\Map::class => [Exposer::class, 'exposeDsMap']];
    /** @var array<string, array{bool, string[]}> */
    private static $enumProperties = [];
    /**
     * @var \Tracy\Dumper\Describer
     */
    private $describer;
    /**
     * @var \Tracy\Dumper\Renderer
     */
    private $renderer;
    /**
     * Dumps variable to the output.
     * @param mixed $var
     * @return mixed
     */
    public static function dump($var, array $options = [])
    {
        if (Helpers::isCli()) {
            $useColors = self::$terminalColors && Helpers::detectColors();
            $dumper = new self($options);
            \fwrite(\STDOUT, $dumper->asTerminal($var, $useColors ? self::$terminalColors : []));
        } elseif (Helpers::isHtmlMode()) {
            $options[self::LOCATION] = $options[self::LOCATION] ?? \true;
            self::renderAssets();
            echo self::toHtml($var, $options);
        } else {
            echo self::toText($var, $options);
        }
        return $var;
    }
    /**
     * Dumps variable to HTML.
     * @param mixed $var
     * @param mixed $key
     */
    public static function toHtml($var, array $options = [], $key = null) : string
    {
        return (new self($options))->asHtml($var, $key);
    }
    /**
     * Dumps variable to plain text.
     * @param mixed $var
     */
    public static function toText($var, array $options = []) : string
    {
        return (new self($options))->asTerminal($var);
    }
    /**
     * Dumps variable to x-terminal.
     * @param mixed $var
     */
    public static function toTerminal($var, array $options = []) : string
    {
        return (new self($options))->asTerminal($var, self::$terminalColors);
    }
    /**
     * Renders <script> & <style>
     */
    public static function renderAssets() : void
    {
        static $sent;
        if (Debugger::$productionMode === \true || $sent) {
            return;
        }
        $sent = \true;
        $nonce = Helpers::getNonce();
        $nonceAttr = $nonce ? ' nonce="' . Helpers::escapeHtml($nonce) . '"' : '';
        $s = \file_get_contents(__DIR__ . '/../assets/toggle.css') . \file_get_contents(__DIR__ . '/assets/dumper-light.css') . \file_get_contents(__DIR__ . '/assets/dumper-dark.css');
        echo "<style{$nonceAttr}>", \str_replace('</', '<\\/', Helpers::minifyCss($s)), "</style>\n";
        if (!Debugger::isEnabled()) {
            $s = '(function(){' . \file_get_contents(__DIR__ . '/../assets/toggle.js') . '})();' . '(function(){' . \file_get_contents(__DIR__ . '/../Dumper/assets/dumper.js') . '})();';
            echo "<script{$nonceAttr}>", \str_replace(['<!--', '</s'], ['<\\!--', '<\\/s'], Helpers::minifyJs($s)), "</script>\n";
        }
    }
    private function __construct(array $options = [])
    {
        $location = $options[self::LOCATION] ?? 0;
        $location = $location === \true ? ~0 : (int) $location;
        $describer = $this->describer = new Describer();
        $describer->maxDepth = (int) ($options[self::DEPTH] ?? $describer->maxDepth);
        $describer->maxLength = (int) ($options[self::TRUNCATE] ?? $describer->maxLength);
        $describer->maxItems = (int) ($options[self::ITEMS] ?? $describer->maxItems);
        $describer->debugInfo = (bool) ($options[self::DEBUGINFO] ?? $describer->debugInfo);
        $describer->scrubber = $options[self::SCRUBBER] ?? $describer->scrubber;
        $describer->keysToHide = \array_flip(\array_map('strtolower', $options[self::KEYS_TO_HIDE] ?? []));
        $describer->resourceExposers = ($options['resourceExporters'] ?? []) + self::$resources;
        $describer->objectExposers = ($options[self::OBJECT_EXPORTERS] ?? []) + self::$objectExporters;
        $describer->enumProperties = self::$enumProperties;
        $describer->location = (bool) $location;
        if ($options[self::LIVE] ?? \false) {
            $tmp =& self::$liveSnapshot;
        } elseif (isset($options[self::SNAPSHOT])) {
            $tmp =& $options[self::SNAPSHOT];
        }
        if (isset($tmp)) {
            $tmp[0] = $tmp[0] ?? [];
            $tmp[1] = $tmp[1] ?? [];
            $describer->snapshot =& $tmp[0];
            $describer->references =& $tmp[1];
        }
        $renderer = $this->renderer = new Renderer();
        $renderer->collapseTop = $options[self::COLLAPSE] ?? $renderer->collapseTop;
        $renderer->collapseSub = $options[self::COLLAPSE_COUNT] ?? $renderer->collapseSub;
        $renderer->collectingMode = isset($options[self::SNAPSHOT]) || !empty($options[self::LIVE]);
        $renderer->lazy = $renderer->collectingMode ? \true : $options[self::LAZY] ?? $renderer->lazy;
        $renderer->sourceLocation = !(~$location & self::LOCATION_SOURCE);
        $renderer->classLocation = !(~$location & self::LOCATION_CLASS);
        $renderer->theme = $options[self::THEME] ?? $renderer->theme ?: null;
        $renderer->hash = $options[self::HASH] ?? \true;
    }
    /**
     * Dumps variable to HTML.
     * @param mixed $var
     * @param mixed $key
     */
    private function asHtml($var, $key = null) : string
    {
        if ($key === null) {
            $model = $this->describer->describe($var);
        } else {
            $model = $this->describer->describe([$key => $var]);
            $model->value = $model->value[0][1];
        }
        return $this->renderer->renderAsHtml($model);
    }
    /**
     * Dumps variable to x-terminal.
     * @param mixed $var
     */
    private function asTerminal($var, array $colors = []) : string
    {
        $model = $this->describer->describe($var);
        return $this->renderer->renderAsText($model, $colors);
    }
    public static function formatSnapshotAttribute(array &$snapshot) : string
    {
        $res = "'" . Renderer::jsonEncode($snapshot[0] ?? []) . "'";
        $snapshot = [];
        return $res;
    }
    public static function addEnumProperty(string $class, string $property, array $constants, bool $set = \false) : void
    {
        self::$enumProperties["{$class}::{$property}"] = [$set, $constants];
    }
}
