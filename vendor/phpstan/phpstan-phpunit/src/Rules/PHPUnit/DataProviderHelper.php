<?php

declare (strict_types=1);
namespace PHPStan\Rules\PHPUnit;

use PHPStan\Analyser\Scope;
use PHPStan\PhpDoc\ResolvedPhpDocBlock;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\MissingMethodFromReflectionException;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Rules\RuleError;
use PHPStan\Rules\RuleErrorBuilder;
use function array_merge;
use function count;
use function explode;
use function preg_match;
use function sprintf;
class DataProviderHelper
{
    /**
     * Reflection provider.
     *
     * @var ReflectionProvider
     */
    private $reflectionProvider;
    /** @var bool */
    private $phpunit10OrNewer;
    public function __construct(ReflectionProvider $reflectionProvider, bool $phpunit10OrNewer)
    {
        $this->reflectionProvider = $reflectionProvider;
        $this->phpunit10OrNewer = $phpunit10OrNewer;
    }
    /**
     * @return array<PhpDocTagNode>
     */
    public function getDataProviderAnnotations(?ResolvedPhpDocBlock $phpDoc) : array
    {
        if ($phpDoc === null) {
            return [];
        }
        $phpDocNodes = $phpDoc->getPhpDocNodes();
        $annotations = [];
        foreach ($phpDocNodes as $docNode) {
            $annotations = array_merge($annotations, $docNode->getTagsByName('@dataProvider'));
        }
        return $annotations;
    }
    /**
     * @return RuleError[] errors
     */
    public function processDataProvider(Scope $scope, PhpDocTagNode $phpDocTag, bool $checkFunctionNameCase, bool $deprecationRulesInstalled) : array
    {
        $dataProviderValue = $this->getDataProviderValue($phpDocTag);
        if ($dataProviderValue === null) {
            // Missing value is already handled in NoMissingSpaceInMethodAnnotationRule
            return [];
        }
        [$classReflection, $method] = $this->parseDataProviderValue($scope, $dataProviderValue);
        if ($classReflection === null) {
            $error = RuleErrorBuilder::message(sprintf('@dataProvider %s related class not found.', $dataProviderValue))->build();
            return [$error];
        }
        try {
            $dataProviderMethodReflection = $classReflection->getNativeMethod($method);
        } catch (MissingMethodFromReflectionException $missingMethodFromReflectionException) {
            $error = RuleErrorBuilder::message(sprintf('@dataProvider %s related method not found.', $dataProviderValue))->build();
            return [$error];
        }
        $errors = [];
        if ($checkFunctionNameCase && $method !== $dataProviderMethodReflection->getName()) {
            $errors[] = RuleErrorBuilder::message(sprintf('@dataProvider %s related method is used with incorrect case: %s.', $dataProviderValue, $dataProviderMethodReflection->getName()))->build();
        }
        if (!$dataProviderMethodReflection->isPublic()) {
            $errors[] = RuleErrorBuilder::message(sprintf('@dataProvider %s related method must be public.', $dataProviderValue))->build();
        }
        if ($deprecationRulesInstalled && $this->phpunit10OrNewer && !$dataProviderMethodReflection->isStatic()) {
            $errors[] = RuleErrorBuilder::message(sprintf('@dataProvider %s related method must be static in PHPUnit 10 and newer.', $dataProviderValue))->build();
        }
        return $errors;
    }
    private function getDataProviderValue(PhpDocTagNode $phpDocTag) : ?string
    {
        if (preg_match('/^[^ \\t]+/', (string) $phpDocTag->value, $matches) !== 1) {
            return null;
        }
        return $matches[0];
    }
    /**
     * @return array{ClassReflection|null, string}
     */
    private function parseDataProviderValue(Scope $scope, string $dataProviderValue) : array
    {
        $parts = explode('::', $dataProviderValue, 2);
        if (count($parts) <= 1) {
            return [$scope->getClassReflection(), $dataProviderValue];
        }
        if ($this->reflectionProvider->hasClass($parts[0])) {
            return [$this->reflectionProvider->getClass($parts[0]), $parts[1]];
        }
        return [null, $dataProviderValue];
    }
}
