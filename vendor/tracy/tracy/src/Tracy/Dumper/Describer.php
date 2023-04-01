<?php

/**
 * This file is part of the Tracy (https://tracy.nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */
declare (strict_types=1);
namespace RectorPrefix202304\Tracy\Dumper;

use RectorPrefix202304\Tracy;
use RectorPrefix202304\Tracy\Helpers;
/**
 * Converts PHP values to internal representation.
 * @internal
 */
final class Describer
{
    public const HiddenValue = '*****';
    // Number.MAX_SAFE_INTEGER
    private const JsSafeInteger = 1 << 53 - 1;
    /**
     * @var int
     */
    public $maxDepth = 7;
    /**
     * @var int
     */
    public $maxLength = 150;
    /**
     * @var int
     */
    public $maxItems = 100;
    /** @var Value[] */
    public $snapshot = [];
    /**
     * @var bool
     */
    public $debugInfo = \false;
    /**
     * @var mixed[]
     */
    public $keysToHide = [];
    /** @var (callable(string, mixed): bool)|null */
    public $scrubber;
    /**
     * @var bool
     */
    public $location = \false;
    /** @var callable[] */
    public $resourceExposers = [];
    /** @var array<string,callable> */
    public $objectExposers = [];
    /** @var array<string, array{bool, string[]}> */
    public $enumProperties = [];
    /** @var (int|\stdClass)[] */
    public $references = [];
    /**
     * @param mixed $var
     */
    public function describe($var) : \stdClass
    {
        \uksort($this->objectExposers, function ($a, $b) : int {
            return $b === '' || \class_exists($a, \false) && \is_subclass_of($a, $b) ? -1 : 1;
        });
        try {
            return (object) ['value' => $this->describeVar($var), 'snapshot' => $this->snapshot, 'location' => $this->location ? self::findLocation() : null];
        } finally {
            $free = [[], []];
            $this->snapshot =& $free[0];
            $this->references =& $free[1];
        }
    }
    /**
     * @param mixed $var
     * @return mixed
     */
    private function describeVar($var, int $depth = 0, ?int $refId = null)
    {
        if ($var === null || \is_bool($var)) {
            return $var;
        }
        $m = 'describe' . \explode(' ', \gettype($var))[0];
        return $this->{$m}($var, $depth, $refId);
    }
    /**
     * @return \Tracy\Dumper\Value|int
     */
    private function describeInteger(int $num)
    {
        return $num <= self::JsSafeInteger && $num >= -self::JsSafeInteger ? $num : new Value(Value::TypeNumber, "{$num}");
    }
    /**
     * @return \Tracy\Dumper\Value|float
     */
    private function describeDouble(float $num)
    {
        if (!\is_finite($num)) {
            return new Value(Value::TypeNumber, (string) $num);
        }
        $js = \json_encode($num);
        return \strpos($js, '.') ? $num : new Value(Value::TypeNumber, "{$js}.0");
        // to distinct int and float in JS
    }
    /**
     * @return \Tracy\Dumper\Value|string
     */
    private function describeString(string $s, int $depth = 0)
    {
        $encoded = Helpers::encodeString($s, $depth ? $this->maxLength : null);
        if ($encoded === $s) {
            return $encoded;
        } elseif (Helpers::isUtf8($s)) {
            return new Value(Value::TypeStringHtml, $encoded, Helpers::utf8Length($s));
        } else {
            return new Value(Value::TypeBinaryHtml, $encoded, \strlen($s));
        }
    }
    /**
     * @return \Tracy\Dumper\Value|mixed[]
     */
    private function describeArray(array $arr, int $depth = 0, ?int $refId = null)
    {
        if ($refId) {
            $res = new Value(Value::TypeRef, 'p' . $refId);
            $value =& $this->snapshot[$res->value];
            if ($value && $value->depth <= $depth) {
                return $res;
            }
            $value = new Value(Value::TypeArray);
            $value->id = $res->value;
            $value->depth = $depth;
            if ($this->maxDepth && $depth >= $this->maxDepth) {
                $value->length = \count($arr);
                return $res;
            } elseif ($depth && $this->maxItems && \count($arr) > $this->maxItems) {
                $value->length = \count($arr);
                $arr = \array_slice($arr, 0, $this->maxItems, \true);
            }
            $items =& $value->items;
        } elseif ($arr && $this->maxDepth && $depth >= $this->maxDepth) {
            return new Value(Value::TypeArray, null, \count($arr));
        } elseif ($depth && $this->maxItems && \count($arr) > $this->maxItems) {
            $res = new Value(Value::TypeArray, null, \count($arr));
            $res->depth = $depth;
            $items =& $res->items;
            $arr = \array_slice($arr, 0, $this->maxItems, \true);
        }
        $items = [];
        foreach ($arr as $k => $v) {
            $refId = $this->getReferenceId($arr, $k);
            $items[] = [$this->describeVar($k, $depth + 1), $this->isSensitive((string) $k, $v) ? new Value(Value::TypeText, self::hideValue($v)) : $this->describeVar($v, $depth + 1, $refId)] + ($refId ? [2 => $refId] : []);
        }
        return $res ?? $items;
    }
    private function describeObject(object $obj, int $depth = 0) : Value
    {
        $id = \spl_object_id($obj);
        $value =& $this->snapshot[$id];
        if ($value && $value->depth <= $depth) {
            return new Value(Value::TypeRef, $id);
        }
        $value = new Value(Value::TypeObject, \get_debug_type($obj));
        $value->id = $id;
        $value->depth = $depth;
        $value->holder = $obj;
        // to be not released by garbage collector in collecting mode
        if ($this->location) {
            $rc = $obj instanceof \Closure ? new \ReflectionFunction($obj) : new \ReflectionClass($obj);
            if ($rc->getFileName() && ($editor = Helpers::editorUri($rc->getFileName(), $rc->getStartLine()))) {
                $value->editor = (object) ['file' => $rc->getFileName(), 'line' => $rc->getStartLine(), 'url' => $editor];
            }
        }
        if ($this->maxDepth && $depth < $this->maxDepth) {
            $value->items = [];
            $props = $this->exposeObject($obj, $value);
            foreach ($props ?? [] as $k => $v) {
                $this->addPropertyTo($value, (string) $k, $v, Value::PropertyVirtual, $this->getReferenceId($props, $k));
            }
        }
        return new Value(Value::TypeRef, $id);
    }
    /**
     * @param  resource  $resource
     */
    private function describeResource($resource, int $depth = 0) : Value
    {
        $id = 'r' . (int) $resource;
        $value =& $this->snapshot[$id];
        if (!$value) {
            $type = \is_resource($resource) ? \get_resource_type($resource) : 'closed';
            $value = new Value(Value::TypeResource, $type . ' resource');
            $value->id = $id;
            $value->depth = $depth;
            $value->items = [];
            if (isset($this->resourceExposers[$type])) {
                foreach ($this->resourceExposers[$type]($resource) as $k => $v) {
                    $value->items[] = [\htmlspecialchars($k), $this->describeVar($v, $depth + 1)];
                }
            }
        }
        return new Value(Value::TypeRef, $id);
    }
    /**
     * @return \Tracy\Dumper\Value|string
     */
    public function describeKey(string $key)
    {
        if (\preg_match('#^[\\w!\\#$%&*+./;<>?@^{|}~-]{1,50}$#D', $key) && !\preg_match('#^(true|false|null)$#iD', $key)) {
            return $key;
        }
        $value = $this->describeString($key);
        return \is_string($value) ? new Value(Value::TypeStringHtml, $key, Helpers::utf8Length($key)) : $value;
    }
    /**
     * @param mixed $v
     */
    public function addPropertyTo(Value $value, string $k, $v, int $type = Value::PropertyVirtual, ?int $refId = null, ?string $class = null, ?Value $described = null) : void
    {
        if ($value->depth && $this->maxItems && \count($value->items ?? []) >= $this->maxItems) {
            $value->length = ($value->length ?? \count($value->items)) + 1;
            return;
        }
        $class = $class ?? $value->value;
        $value->items[] = [$this->describeKey($k), $type !== Value::PropertyVirtual && $this->isSensitive($k, $v, $class) ? new Value(Value::TypeText, self::hideValue($v)) : $described ?? $this->describeVar($v, $value->depth + 1, $refId), $type === Value::PropertyPrivate ? $class : $type] + ($refId ? [3 => $refId] : []);
    }
    private function exposeObject(object $obj, Value $value) : ?array
    {
        foreach ($this->objectExposers as $type => $dumper) {
            if (!$type || $obj instanceof $type) {
                return $dumper($obj, $value, $this);
            }
        }
        if ($this->debugInfo && \method_exists($obj, '__debugInfo')) {
            return $obj->__debugInfo();
        }
        Exposer::exposeObject($obj, $value, $this);
        return null;
    }
    /**
     * @param mixed $val
     */
    private function isSensitive(string $key, $val, ?string $class = null) : bool
    {
        return $val instanceof \SensitiveParameterValue || $this->scrubber !== null && ($this->scrubber)($key, $val, $class) || isset($this->keysToHide[\strtolower($key)]) || isset($this->keysToHide[\strtolower($class . '::$' . $key)]);
    }
    /**
     * @param mixed $val
     */
    private static function hideValue($val) : string
    {
        if ($val instanceof \SensitiveParameterValue) {
            $val = $val->getValue();
        }
        return self::HiddenValue . ' (' . \get_debug_type($val) . ')';
    }
    /**
     * @param mixed $value
     */
    public function describeEnumProperty(string $class, string $property, $value) : ?Value
    {
        [$set, $constants] = $this->enumProperties["{$class}::{$property}"] ?? null;
        if (!\is_int($value) || !$constants || !($constants = Helpers::decomposeFlags($value, $set, $constants))) {
            return null;
        }
        $constants = \array_map(function (string $const) use($class) : string {
            return \str_replace("{$class}::", 'self::', $const);
        }, $constants);
        return new Value(Value::TypeNumber, \implode(' | ', $constants) . " ({$value})");
    }
    /**
     * @param string|int $key
     */
    public function getReferenceId(array $arr, $key) : ?int
    {
        return ($rr = \ReflectionReference::fromArrayElement($arr, $key)) ? $this->references[$rr->getId()] = $this->references[$rr->getId()] ?? \count($this->references) + 1 : null;
    }
    /**
     * Finds the location where dump was called. Returns [file, line, code]
     */
    private static function findLocation() : ?array
    {
        foreach (\debug_backtrace(\DEBUG_BACKTRACE_IGNORE_ARGS) as $item) {
            if (isset($item['class']) && ($item['class'] === self::class || $item['class'] === Tracy\Dumper::class)) {
                $location = $item;
                continue;
            } elseif (isset($item['function'])) {
                try {
                    $reflection = isset($item['class']) ? new \ReflectionMethod($item['class'], $item['function']) : new \ReflectionFunction($item['function']);
                    if ($reflection->isInternal() || \preg_match('#\\s@tracySkipLocation\\s#', (string) $reflection->getDocComment())) {
                        $location = $item;
                        continue;
                    }
                } catch (\ReflectionException $e) {
                }
            }
            break;
        }
        if (isset($location['file'], $location['line']) && \is_file($location['file'])) {
            $lines = \file($location['file']);
            $line = $lines[$location['line'] - 1];
            return [$location['file'], $location['line'], \trim(\preg_match('#\\w*dump(er::\\w+)?\\(.*\\)#i', $line, $m) ? $m[0] : $line)];
        }
        return null;
    }
}
