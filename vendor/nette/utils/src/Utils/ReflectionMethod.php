<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */
declare (strict_types=1);
namespace RectorPrefix202602\Nette\Utils;

use function explode, is_string, str_contains;
/**
 * ReflectionMethod preserving the original class name.
 * @internal
 */
final class ReflectionMethod extends \ReflectionMethod
{
    /** @var \ReflectionClass<object>
     * @readonly */
    private \ReflectionClass $originalClass;
    /** @param  class-string|object  $objectOrMethod */
    public function __construct($objectOrMethod, ?string $method = null)
    {
        if (is_string($objectOrMethod) && strpos($objectOrMethod, '::') !== \false) {
            [$objectOrMethod, $method] = explode('::', $objectOrMethod, 2);
        }
        parent::__construct($objectOrMethod, $method);
        $this->originalClass = new \ReflectionClass($objectOrMethod);
    }
    /** @return \ReflectionClass<object> */
    public function getOriginalClass(): \ReflectionClass
    {
        return $this->originalClass;
    }
}
