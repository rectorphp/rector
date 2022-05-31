<?php

declare (strict_types=1);
namespace Rector\Nette\Latte\Parser;

use RectorPrefix20220531\Nette\Utils\Strings;
use PHPStan\BetterReflection\Reflection\ReflectionClass;
use PHPStan\BetterReflection\Reflection\ReflectionNamedType;
use PHPStan\BetterReflection\Reflector\Exception\IdentifierNotFound;
use Rector\Nette\ValueObject\LatteVariableType;
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
        $templateTypeMatch = \RectorPrefix20220531\Nette\Utils\Strings::match($content, self::TEMPLATE_TYPE_REGEX);
        if (!isset($templateTypeMatch['template'])) {
            return [];
        }
        try {
            $reflectionClass = \PHPStan\BetterReflection\Reflection\ReflectionClass::createFromName($templateTypeMatch['template']);
        } catch (\PHPStan\BetterReflection\Reflector\Exception\IdentifierNotFound $exception) {
            return [];
        }
        $variableTypes = [];
        foreach ($reflectionClass->getProperties() as $property) {
            /** @var ReflectionNamedType $type */
            $type = $property->getType();
            $variableTypes[] = new \Rector\Nette\ValueObject\LatteVariableType($property->getName(), (string) $type);
        }
        return $variableTypes;
    }
}
