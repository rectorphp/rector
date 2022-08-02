<?php

/**
 * This file is part of the Tracy (https://tracy.nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */
declare (strict_types=1);
namespace RectorPrefix202208\Tracy\Dumper;

use RectorPrefix202208\Tracy\Helpers;
/**
 * Visualisation of internal representation.
 * @internal
 */
final class Renderer
{
    private const TypeArrayKey = 'array';
    /** @var int|bool */
    public $collapseTop = 14;
    /** @var int */
    public $collapseSub = 7;
    /** @var bool */
    public $classLocation = \false;
    /** @var bool */
    public $sourceLocation = \false;
    /** @var bool|null  lazy-loading via JavaScript? true=full, false=none, null=collapsed parts */
    public $lazy;
    /** @var bool */
    public $hash = \true;
    /** @var string */
    public $theme = 'light';
    /** @var bool */
    public $collectingMode = \false;
    /** @var Value[] */
    private $snapshot = [];
    /** @var Value[]|null */
    private $snapshotSelection;
    /** @var array */
    private $parents = [];
    /** @var array */
    private $above = [];
    public function renderAsHtml(\stdClass $model) : string
    {
        try {
            $value = $model->value;
            $this->snapshot = $model->snapshot;
            if ($this->lazy === \false) {
                // no lazy-loading
                $html = $this->renderVar($value);
                $json = $snapshot = null;
            } elseif ($this->lazy && (\is_array($value) && $value || \is_object($value))) {
                // full lazy-loading
                $html = '';
                $snapshot = $this->collectingMode ? null : $this->snapshot;
                $json = $value;
            } else {
                // lazy-loading of collapsed parts
                $html = $this->renderVar($value);
                $snapshot = $this->snapshotSelection;
                $json = null;
            }
        } finally {
            $this->parents = $this->snapshot = $this->above = [];
            $this->snapshotSelection = null;
        }
        $location = null;
        if ($model->location && $this->sourceLocation) {
            [$file, $line, $code] = $model->location;
            $uri = Helpers::editorUri($file, $line);
            $location = Helpers::formatHtml('<a href="%" class="tracy-dump-location" title="in file % on line %%">', $uri ?? '#', $file, $line, $uri ? "\nClick to open in editor" : '') . Helpers::encodeString($code, 50) . " 📍</a\n>";
        }
        return '<pre class="tracy-dump' . ($this->theme ? ' tracy-' . \htmlspecialchars($this->theme) : '') . ($json && $this->collapseTop === \true ? ' tracy-collapsed' : '') . '"' . ($snapshot !== null ? " data-tracy-snapshot='" . self::jsonEncode($snapshot) . "'" : '') . ($json ? " data-tracy-dump='" . self::jsonEncode($json) . "'" : '') . ($location || \strlen($html) > 100 ? "\n" : '') . '>' . $location . $html . "</pre>\n";
    }
    public function renderAsText(\stdClass $model, array $colors = []) : string
    {
        try {
            $this->snapshot = $model->snapshot;
            $this->lazy = \false;
            $s = $this->renderVar($model->value);
        } finally {
            $this->parents = $this->snapshot = $this->above = [];
        }
        $s = $colors ? self::htmlToAnsi($s, $colors) : $s;
        $s = \htmlspecialchars_decode(\strip_tags($s), \ENT_QUOTES | \ENT_HTML5);
        $s = \str_replace('…', '...', $s);
        $s .= \substr($s, -1) === "\n" ? '' : "\n";
        if ($this->sourceLocation && ([$file, $line] = $model->location)) {
            $s .= "in {$file}:{$line}\n";
        }
        return $s;
    }
    /**
     * @param  mixed  $value
     * @param  string|int|null  $keyType
     */
    private function renderVar($value, int $depth = 0, $keyType = null) : string
    {
        switch (\true) {
            case $value === null:
                return '<span class="tracy-dump-null">null</span>';
            case \is_bool($value):
                return '<span class="tracy-dump-bool">' . ($value ? 'true' : 'false') . '</span>';
            case \is_int($value):
                return '<span class="tracy-dump-number">' . $value . '</span>';
            case \is_float($value):
                return '<span class="tracy-dump-number">' . self::jsonEncode($value) . '</span>';
            case \is_string($value):
                return $this->renderString($value, $depth, $keyType);
            case \is_array($value):
            case $value->type === Value::TYPE_ARRAY:
                return $this->renderArray($value, $depth);
            case $value->type === Value::TYPE_REF:
                return $this->renderVar($this->snapshot[$value->value], $depth, $keyType);
            case $value->type === Value::TYPE_OBJECT:
                return $this->renderObject($value, $depth);
            case $value->type === Value::TYPE_NUMBER:
                return '<span class="tracy-dump-number">' . Helpers::escapeHtml($value->value) . '</span>';
            case $value->type === Value::TYPE_TEXT:
                return '<span class="tracy-dump-virtual">' . Helpers::escapeHtml($value->value) . '</span>';
            case $value->type === Value::TYPE_STRING_HTML:
            case $value->type === Value::TYPE_BINARY_HTML:
                return $this->renderString($value, $depth, $keyType);
            case $value->type === Value::TYPE_RESOURCE:
                return $this->renderResource($value, $depth);
            default:
                throw new \Exception('Unknown type');
        }
    }
    /**
     * @param  string|Value  $str
     * @param  string|int|null  $keyType
     */
    private function renderString($str, int $depth, $keyType) : string
    {
        if ($keyType === self::TypeArrayKey) {
            $indent = '<span class="tracy-dump-indent">   ' . \str_repeat('|  ', $depth - 1) . ' </span>';
            return '<span class="tracy-dump-string">' . "<span class='tracy-dump-lq'>'</span>" . (\is_string($str) ? Helpers::escapeHtml($str) : \str_replace("\n", "\n" . $indent, $str->value)) . "<span>'</span>" . '</span>';
        } elseif ($keyType !== null) {
            $classes = [Value::PROP_PUBLIC => 'tracy-dump-public', Value::PROP_PROTECTED => 'tracy-dump-protected', Value::PROP_DYNAMIC => 'tracy-dump-dynamic', Value::PROP_VIRTUAL => 'tracy-dump-virtual'];
            $indent = '<span class="tracy-dump-indent">   ' . \str_repeat('|  ', $depth - 1) . ' </span>';
            $title = \is_string($keyType) ? ' title="declared in ' . Helpers::escapeHtml($keyType) . '"' : null;
            return '<span class="' . ($title ? 'tracy-dump-private' : $classes[$keyType]) . '"' . $title . '>' . (\is_string($str) ? Helpers::escapeHtml($str) : "<span class='tracy-dump-lq'>'</span>" . \str_replace("\n", "\n" . $indent, $str->value) . "<span>'</span>") . '</span>';
        } elseif (\is_string($str)) {
            $len = Helpers::utf8Length($str);
            return '<span class="tracy-dump-string"' . ($len > 1 ? ' title="' . $len . ' characters"' : '') . '>' . "<span>'</span>" . Helpers::escapeHtml($str) . "<span>'</span>" . '</span>';
        } else {
            $unit = $str->type === Value::TYPE_STRING_HTML ? 'characters' : 'bytes';
            $count = \substr_count($str->value, "\n");
            if ($count) {
                $collapsed = $indent1 = $toggle = null;
                $indent = '<span class="tracy-dump-indent"> </span>';
                if ($depth) {
                    $collapsed = $count >= $this->collapseSub;
                    $indent1 = '<span class="tracy-dump-indent">   ' . \str_repeat('|  ', $depth) . '</span>';
                    $indent = '<span class="tracy-dump-indent">   ' . \str_repeat('|  ', $depth) . ' </span>';
                    $toggle = '<span class="tracy-toggle' . ($collapsed ? ' tracy-collapsed' : '') . '">string</span>' . "\n";
                }
                return $toggle . '<div class="tracy-dump-string' . ($collapsed ? ' tracy-collapsed' : '') . '" title="' . $str->length . ' ' . $unit . '">' . $indent1 . '<span' . ($count ? ' class="tracy-dump-lq"' : '') . ">'</span>" . \str_replace("\n", "\n" . $indent, $str->value) . "<span>'</span>" . ($depth ? "\n" : '') . '</div>';
            }
            return '<span class="tracy-dump-string"' . ($str->length > 1 ? " title=\"{$str->length} {$unit}\"" : '') . '>' . "<span>'</span>" . $str->value . "<span>'</span>" . '</span>';
        }
    }
    /**
     * @param  array|Value  $array
     */
    private function renderArray($array, int $depth) : string
    {
        $out = '<span class="tracy-dump-array">array</span> (';
        if (\is_array($array)) {
            $items = $array;
            $count = \count($items);
            $out .= $count . ')';
        } elseif ($array->items === null) {
            return $out . $array->length . ') …';
        } else {
            $items = $array->items;
            $count = $array->length ?? \count($items);
            $out .= $count . ')';
            if ($array->id && isset($this->parents[$array->id])) {
                return $out . ' <i>RECURSION</i>';
            } elseif ($array->id && ($array->depth < $depth || isset($this->above[$array->id]))) {
                if ($this->lazy !== \false) {
                    $ref = new Value(Value::TYPE_REF, $array->id);
                    $this->copySnapshot($ref);
                    return '<span class="tracy-toggle tracy-collapsed" data-tracy-dump=\'' . \json_encode($ref) . "'>" . $out . '</span>';
                } elseif ($this->hash) {
                    return $out . (isset($this->above[$array->id]) ? ' <i>see above</i>' : ' <i>see below</i>');
                }
            }
        }
        if (!$count) {
            return $out;
        }
        $collapsed = $depth ? $this->lazy === \false || $depth === 1 ? $count >= $this->collapseSub : \true : (\is_int($this->collapseTop) ? $count >= $this->collapseTop : $this->collapseTop);
        $span = '<span class="tracy-toggle' . ($collapsed ? ' tracy-collapsed' : '') . '"';
        if ($collapsed && $this->lazy !== \false) {
            $array = isset($array->id) ? new Value(Value::TYPE_REF, $array->id) : $array;
            $this->copySnapshot($array);
            return $span . " data-tracy-dump='" . self::jsonEncode($array) . "'>" . $out . '</span>';
        }
        $out = $span . '>' . $out . "</span>\n" . '<div' . ($collapsed ? ' class="tracy-collapsed"' : '') . '>';
        $indent = '<span class="tracy-dump-indent">   ' . \str_repeat('|  ', $depth) . '</span>';
        $this->parents[$array->id ?? null] = $this->above[$array->id ?? null] = \true;
        foreach ($items as $info) {
            [$k, $v, $ref] = $info + [2 => null];
            $out .= $indent . $this->renderVar($k, $depth + 1, self::TypeArrayKey) . ' => ' . ($ref && $this->hash ? '<span class="tracy-dump-hash">&' . $ref . '</span> ' : '') . ($tmp = $this->renderVar($v, $depth + 1)) . (\substr($tmp, -6) === '</div>' ? '' : "\n");
        }
        if ($count > \count($items)) {
            $out .= $indent . "…\n";
        }
        unset($this->parents[$array->id ?? null]);
        return $out . '</div>';
    }
    private function renderObject(Value $object, int $depth) : string
    {
        $editorAttributes = '';
        if ($this->classLocation && $object->editor) {
            $editorAttributes = Helpers::formatHtml(' title="Declared in file % on line %%%" data-tracy-href="%"', $object->editor->file, $object->editor->line, $object->editor->url ? "\nCtrl-Click to open in editor" : '', "\nAlt-Click to expand/collapse all child nodes", $object->editor->url);
        }
        $pos = \strrpos($object->value, '\\');
        $out = '<span class="tracy-dump-object"' . $editorAttributes . '>' . ($pos ? Helpers::escapeHtml(\substr($object->value, 0, $pos + 1)) . '<b>' . Helpers::escapeHtml(\substr($object->value, $pos + 1)) . '</b>' : Helpers::escapeHtml($object->value)) . '</span>' . ($object->id && $this->hash ? ' <span class="tracy-dump-hash">#' . $object->id . '</span>' : '');
        if ($object->items === null) {
            return $out . ' …';
        } elseif (!$object->items) {
            return $out;
        } elseif ($object->id && isset($this->parents[$object->id])) {
            return $out . ' <i>RECURSION</i>';
        } elseif ($object->id && ($object->depth < $depth || isset($this->above[$object->id]))) {
            if ($this->lazy !== \false) {
                $ref = new Value(Value::TYPE_REF, $object->id);
                $this->copySnapshot($ref);
                return '<span class="tracy-toggle tracy-collapsed" data-tracy-dump=\'' . \json_encode($ref) . "'>" . $out . '</span>';
            } elseif ($this->hash) {
                return $out . (isset($this->above[$object->id]) ? ' <i>see above</i>' : ' <i>see below</i>');
            }
        }
        $collapsed = $object->collapsed ?? ($depth ? $this->lazy === \false || $depth === 1 ? \count($object->items) >= $this->collapseSub : \true : (\is_int($this->collapseTop) ? \count($object->items) >= $this->collapseTop : $this->collapseTop));
        $span = '<span class="tracy-toggle' . ($collapsed ? ' tracy-collapsed' : '') . '"';
        if ($collapsed && $this->lazy !== \false) {
            $value = $object->id ? new Value(Value::TYPE_REF, $object->id) : $object;
            $this->copySnapshot($value);
            return $span . " data-tracy-dump='" . self::jsonEncode($value) . "'>" . $out . '</span>';
        }
        $out = $span . '>' . $out . "</span>\n" . '<div' . ($collapsed ? ' class="tracy-collapsed"' : '') . '>';
        $indent = '<span class="tracy-dump-indent">   ' . \str_repeat('|  ', $depth) . '</span>';
        $this->parents[$object->id] = $this->above[$object->id] = \true;
        foreach ($object->items as $info) {
            [$k, $v, $type, $ref] = $info + [2 => Value::PROP_VIRTUAL, null];
            $out .= $indent . $this->renderVar($k, $depth + 1, $type) . ': ' . ($ref && $this->hash ? '<span class="tracy-dump-hash">&' . $ref . '</span> ' : '') . ($tmp = $this->renderVar($v, $depth + 1)) . (\substr($tmp, -6) === '</div>' ? '' : "\n");
        }
        if ($object->length > \count($object->items)) {
            $out .= $indent . "…\n";
        }
        unset($this->parents[$object->id]);
        return $out . '</div>';
    }
    private function renderResource(Value $resource, int $depth) : string
    {
        $out = '<span class="tracy-dump-resource">' . Helpers::escapeHtml($resource->value) . '</span> ' . ($this->hash ? '<span class="tracy-dump-hash">@' . \substr($resource->id, 1) . '</span>' : '');
        if (!$resource->items) {
            return $out;
        } elseif (isset($this->above[$resource->id])) {
            if ($this->lazy !== \false) {
                $ref = new Value(Value::TYPE_REF, $resource->id);
                $this->copySnapshot($ref);
                return '<span class="tracy-toggle tracy-collapsed" data-tracy-dump=\'' . \json_encode($ref) . "'>" . $out . '</span>';
            }
            return $out . ' <i>see above</i>';
        } else {
            $this->above[$resource->id] = \true;
            $out = "<span class=\"tracy-toggle tracy-collapsed\">{$out}</span>\n<div class=\"tracy-collapsed\">";
            foreach ($resource->items as [$k, $v]) {
                $out .= '<span class="tracy-dump-indent">   ' . \str_repeat('|  ', $depth) . '</span>' . $this->renderVar($k, $depth + 1, Value::PROP_VIRTUAL) . ': ' . ($tmp = $this->renderVar($v, $depth + 1)) . (\substr($tmp, -6) === '</div>' ? '' : "\n");
            }
            return $out . '</div>';
        }
    }
    private function copySnapshot($value) : void
    {
        if ($this->collectingMode) {
            return;
        }
        if ($this->snapshotSelection === null) {
            $this->snapshotSelection = [];
        }
        if (\is_array($value)) {
            foreach ($value as [, $v]) {
                $this->copySnapshot($v);
            }
        } elseif ($value instanceof Value && $value->type === Value::TYPE_REF) {
            if (!isset($this->snapshotSelection[$value->value])) {
                $ref = $this->snapshotSelection[$value->value] = $this->snapshot[$value->value];
                $this->copySnapshot($ref);
            }
        } elseif ($value instanceof Value && $value->items) {
            foreach ($value->items as [, $v]) {
                $this->copySnapshot($v);
            }
        }
    }
    public static function jsonEncode($snapshot) : string
    {
        $old = @\ini_set('serialize_precision', '-1');
        // @ may be disabled
        try {
            return \json_encode($snapshot, \JSON_HEX_APOS | \JSON_HEX_AMP | \JSON_UNESCAPED_UNICODE | \JSON_UNESCAPED_SLASHES);
        } finally {
            if ($old !== \false) {
                \ini_set('serialize_precision', $old);
            }
        }
    }
    private static function htmlToAnsi(string $s, array $colors) : string
    {
        $stack = ['0'];
        $s = \preg_replace_callback('#<\\w+(?: class="tracy-dump-(\\w+)")?[^>]*>|</\\w+>#', function ($m) use($colors, &$stack) : string {
            if ($m[0][1] === '/') {
                \array_pop($stack);
            } else {
                $stack[] = isset($m[1], $colors[$m[1]]) ? $colors[$m[1]] : '0';
            }
            return "\x1b[" . \end($stack) . 'm';
        }, $s);
        $s = \preg_replace('/\\e\\[0m(\\n*)(?=\\e)/', '$1', $s);
        return $s;
    }
}
