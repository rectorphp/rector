<?php

declare (strict_types=1);
namespace RectorPrefix202609\Entropy\Console\Mapper;

use RectorPrefix202609\Entropy\Attributes\RelatedTest;
use RectorPrefix202609\Entropy\Console\Contract\CommandInterface;
use RectorPrefix202609\Entropy\Console\Exception\InvalidCommandException;
use RectorPrefix202609\Entropy\Console\ValueObject\Argument;
use RectorPrefix202609\Entropy\Console\ValueObject\ArgumentsAndOptions;
use RectorPrefix202609\Entropy\Console\ValueObject\Option;
use RectorPrefix202609\Entropy\Reflection\ParameterDescriptionResolver;
use RectorPrefix202609\Entropy\Reflection\ParameterOptionMarkerResolver;
use RectorPrefix202609\Entropy\Tests\Console\Mapper\CommandRunParametersMapperTest;
use ReflectionMethod;
use ReflectionNamedType;
final class CommandRunParametersMapper
{
    public function map(CommandInterface $command): ArgumentsAndOptions
    {
        $runReflectionMethod = new ReflectionMethod($command, 'run');
        if (\PHP_VERSION_ID < 80100) {
            $runReflectionMethod->setAccessible(\true);
        }
        $paramDescriptions = ParameterDescriptionResolver::resolve($runReflectionMethod);
        $optionMarkers = ParameterOptionMarkerResolver::resolve($runReflectionMethod);
        $arguments = [];
        $options = [];
        foreach ($runReflectionMethod->getParameters() as $key => $reflectionParameter) {
            $parameterType = $reflectionParameter->getType();
            if (!$parameterType instanceof ReflectionNamedType) {
                throw new InvalidCommandException(sprintf('Parameter "%s" of command "%s" must have explicit type declaration', $reflectionParameter->getName(), $command->getName()));
            }
            $parameterName = $reflectionParameter->getName();
            $parameterType = $parameterType->getName();
            $description = $paramDescriptions[$parameterName] ?? null;
            // 1st param is argument by convention
            $acceptsMultipleValue = $parameterType === 'array';
            $defaultValue = null;
            if ($reflectionParameter->isDefaultValueAvailable()) {
                $defaultValue = $reflectionParameter->getDefaultValue();
                // not relevant default value
                if ($defaultValue === []) {
                    $defaultValue = null;
                }
            }
            // first param can be an arg by convention, only "string" and "array" are allowed types,
            // unless explicitly marked as an option via "@option $paramName" in the docblock
            $isExplicitOption = isset($optionMarkers[$parameterName]);
            if ($key === 0 && !$isExplicitOption && in_array($parameterType, ['string', 'array'], \true)) {
                $arguments[] = new Argument($parameterName, $description, $acceptsMultipleValue);
            } else {
                // correct plural argumen to singular --option name
                if ($acceptsMultipleValue && substr_compare($parameterName, 's', -strlen('s')) === 0) {
                    $parameterName = (string) substr($parameterName, 0, -1);
                }
                $options[] = new Option($parameterName, $parameterType, $description, $acceptsMultipleValue, $defaultValue);
            }
        }
        return new ArgumentsAndOptions($arguments, $options);
    }
}
