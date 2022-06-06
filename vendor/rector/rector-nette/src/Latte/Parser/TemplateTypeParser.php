<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\Nette\Latte\Parser;

use RectorPrefix20220606\Nette\Utils\Strings;
use RectorPrefix20220606\PHPStan\BetterReflection\Reflection\ReflectionClass;
use RectorPrefix20220606\PHPStan\BetterReflection\Reflection\ReflectionNamedType;
use RectorPrefix20220606\PHPStan\BetterReflection\Reflector\Exception\IdentifierNotFound;
use RectorPrefix20220606\Rector\Nette\ValueObject\LatteVariableType;
final class TemplateTypeParser
{
    /**
     * @var string
     * @see https://regex101.com/r/R06TTK/1
     */
    private const TEMPLATE_TYPE_REGEX = '#{templateType (?P<template>.*?)}#';
    /**
     * @return LatteVariableType[]
     */
    public function parse(string $content) : array
    {
        $templateTypeMatch = Strings::match($content, self::TEMPLATE_TYPE_REGEX);
        if (!isset($templateTypeMatch['template'])) {
            return [];
        }
        try {
            $reflectionClass = ReflectionClass::createFromName($templateTypeMatch['template']);
        } catch (IdentifierNotFound $exception) {
            return [];
        }
        $variableTypes = [];
        foreach ($reflectionClass->getProperties() as $property) {
            /** @var ReflectionNamedType $type */
            $type = $property->getType();
            $variableTypes[] = new LatteVariableType($property->getName(), (string) $type);
        }
        return $variableTypes;
    }
}
