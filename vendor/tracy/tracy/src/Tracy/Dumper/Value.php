<?php

/**
 * This file is part of the Tracy (https://tracy.nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */
declare (strict_types=1);
namespace RectorPrefix202304\Tracy\Dumper;

/**
 * @internal
 */
final class Value implements \JsonSerializable
{
    public const TypeArray = 'array', TypeBinaryHtml = 'bin', TypeNumber = 'number', TypeObject = 'object', TypeRef = 'ref', TypeResource = 'resource', TypeStringHtml = 'string', TypeText = 'text';
    public const PropertyPublic = 0, PropertyProtected = 1, PropertyPrivate = 2, PropertyDynamic = 3, PropertyVirtual = 4;
    /**
     * @var string
     */
    public $type;
    /**
     * @var string|int|null
     */
    public $value;
    /**
     * @var int|null
     */
    public $length;
    /**
     * @var int|null
     */
    public $depth;
    /**
     * @var int|string|null
     */
    public $id = null;
    /**
     * @var object
     */
    public $holder;
    /**
     * @var mixed[]|null
     */
    public $items;
    /**
     * @var \stdClass|null
     */
    public $editor;
    /**
     * @var bool|null
     */
    public $collapsed;
    /**
     * @param string|int|null $value
     */
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
