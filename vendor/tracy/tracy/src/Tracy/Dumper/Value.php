<?php

/**
 * This file is part of the Tracy (https://tracy.nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */
declare (strict_types=1);
namespace RectorPrefix20220531\Tracy\Dumper;

/**
 * @internal
 */
final class Value implements \JsonSerializable
{
    public const TYPE_ARRAY = 'array', TYPE_BINARY_HTML = 'bin', TYPE_NUMBER = 'number', TYPE_OBJECT = 'object', TYPE_REF = 'ref', TYPE_RESOURCE = 'resource', TYPE_STRING_HTML = 'string', TYPE_TEXT = 'text';
    public const PROP_PUBLIC = 0, PROP_PROTECTED = 1, PROP_PRIVATE = 2, PROP_DYNAMIC = 3, PROP_VIRTUAL = 4;
    /** @var string */
    public $type;
    /** @var string|int */
    public $value;
    /** @var ?int */
    public $length;
    /** @var ?int */
    public $depth;
    /** @var int|string */
    public $id;
    /** @var object */
    public $holder;
    /** @var ?array */
    public $items;
    /** @var ?\stdClass */
    public $editor;
    /** @var ?bool */
    public $collapsed;
    public function __construct(string $type, $value = null, ?int $length = null)
    {
        $this->type = $type;
        $this->value = $value;
        $this->length = $length;
    }
    public function jsonSerialize() : array
    {
        $res = [$this->type => $this->value];
        foreach (['length', 'editor', 'items', 'collapsed'] as $k) {
            if ($this->{$k} !== null) {
                $res[$k] = $this->{$k};
            }
        }
        return $res;
    }
}
