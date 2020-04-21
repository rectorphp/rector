<?php

declare(strict_types=1);

namespace Rector\Core\Scan;

use Nette\Utils\Strings;
use Rector\Core\Exception\NotImplementedException;
use Rector\Core\Rector\ClassMethod\AddReturnTypeDeclarationRector;
use Rector\Core\ValueObject\Scan\Argument;
use Rector\Core\ValueObject\Scan\ClassMethodWithArguments;
use Rector\TypeDeclaration\Rector\ClassMethod\AddParamTypeDeclarationRector;

final class ScannedErrorToRectorResolver
{
    /**
     * @see https://regex101.com/r/RbJy9h/1/
     * @var string
     */
    private const INCOMPATIBLE_PARAM_TYPE_PATTERN = '#Declaration of (?<current>\w.*?) should be compatible with (?<should_be>\w.*?)$#';

    /**
     * @see https://regex101.com/r/D6Z5Uq/1/
     * @var string
     */
    private const INCOMPATIBLE_RETURN_TYPE_PATTERN = '#Declaration of (?<current>\w.*?) must be compatible with (?<should_be>\w.*?)$#';

    /**
     * @see https://regex101.com/r/RbJy9h/8/
     * @var string
     */
    private const CLASS_METHOD_ARGUMENTS_PATTERN = '#(?<class>.*?)::(?<method>.*?)\((?<arguments>.*?)\)(:\s?(?<return_type>\w+))?#';

    /**
     * @see https://regex101.com/r/RbJy9h/5
     * @var string
     */
    private const ARGUMENTS_PATTERN = '#(\b(?<type>\w.*?)?\b )?\$(?<name>\w+)#sm';

    /**
     * @var mixed[]
     */
    private $paramChanges = [];

    /**
     * @var mixed[]
     */
    private $returnChanges = [];

    /**
     * @param string[] $errors
     * @return mixed[]
     */
    public function processErrors(array $errors): array
    {
        $this->paramChanges = [];

        foreach ($errors as $fatalError) {
            $match = Strings::match($fatalError, self::INCOMPATIBLE_PARAM_TYPE_PATTERN);
            if ($match) {
                $this->processIncompatibleParamTypeMatch($match);
                continue;
            }

            $match = Strings::match($fatalError, self::INCOMPATIBLE_RETURN_TYPE_PATTERN);
            if ($match) {
                $this->processIncompatibleReturnTypeMatch($match);
            }
        }

        $config = [];
        if ($this->paramChanges !== []) {
            $config['services'][AddParamTypeDeclarationRector::class]['$typehintForParameterByMethodByClass'] = $this->paramChanges;
        }

        if ($this->returnChanges !== []) {
            $config['services'][AddReturnTypeDeclarationRector::class]['$typehintForMethodByClass'] = $this->returnChanges;
        }

        return $config;
    }

    private function processIncompatibleParamTypeMatch(array $match): void
    {
        if (! Strings::contains($match['current'], '::')) {
            // probably a function?
            throw new NotImplementedException();
        }

        $scannedMethod = $this->createScannedMethod($match['current']);
        $shouldBeMethod = $this->createScannedMethod($match['should_be']);

        $this->collectClassMethodParamDifferences($scannedMethod, $shouldBeMethod);
    }

    private function processIncompatibleReturnTypeMatch(array $match): void
    {
        if (! Strings::contains($match['current'], '::')) {
            // probably a function?
            throw new NotImplementedException();
        }

        $scannedMethod = $this->createScannedMethod($match['current']);
        $shouldBeMethod = $this->createScannedMethod($match['should_be']);

        $this->collectClassMethodReturnDifferences($scannedMethod, $shouldBeMethod);
    }

    private function createScannedMethod(string $classMethodWithArgumentsDescription): ClassMethodWithArguments
    {
        $match = Strings::match($classMethodWithArgumentsDescription, self::CLASS_METHOD_ARGUMENTS_PATTERN);
        if (! $match) {
            throw new NotImplementedException();
        }

        $arguments = $this->createArguments((string) $match['arguments']);

        return new ClassMethodWithArguments(
            $match['class'],
            $match['method'],
            $arguments,
            $match['return_type'] ?? ''
        );
    }

    private function collectClassMethodParamDifferences(
        ClassMethodWithArguments $scannedMethod,
        ClassMethodWithArguments $shouldBeMethod
    ): void {
        foreach ($scannedMethod->getArguments() as $scannedMethodArgument) {
            $shouldBeArgument = $shouldBeMethod->getArgumentByPosition($scannedMethodArgument->getPosition());

            if ($shouldBeArgument === null) {
                throw new NotImplementedException();
            }

            // types are identical, nothing to change
            if ($scannedMethodArgument->getType() === $shouldBeArgument->getType()) {
                continue;
            }

            $this->paramChanges[$scannedMethod->getClass()][$scannedMethod->getMethod()][$scannedMethodArgument->getPosition()] = $shouldBeArgument->getType();
        }
    }

    private function collectClassMethodReturnDifferences(
        ClassMethodWithArguments $scannedMethod,
        ClassMethodWithArguments $shouldBeMethod
    ): void {
        if ($scannedMethod->getReturnType() === $shouldBeMethod->getReturnType()) {
            return;
        }

        $this->returnChanges[$scannedMethod->getClass()][$scannedMethod->getMethod()] = $shouldBeMethod->getReturnType();
    }

    /**
     * @return Argument[]
     */
    private function createArguments(string $argumentsDescription): array
    {
        // 0 arguments
        if ($argumentsDescription === '') {
            return [];
        }

        $arguments = [];
        $argumentDescriptions = Strings::split($argumentsDescription, '#\b,\b#');
        foreach ($argumentDescriptions as $position => $argumentDescription) {
            $match = Strings::match((string) $argumentDescription, self::ARGUMENTS_PATTERN);
            if (! $match) {
                throw new NotImplementedException();
            }

            $arguments[] = new Argument($position, $match['type'] ?? '');
        }

        return $arguments;
    }
}
