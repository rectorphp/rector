<?php

declare (strict_types=1);
namespace RectorPrefix202609\TomasVotruba\UnusedPublic\Templates;

final class UsedMethodAnalyzer
{
    /**
     * @param string[] $twigMethodNames
     */
    public function isUsedInTwig(string $methodName, array $twigMethodNames): bool
    {
        if ($twigMethodNames === []) {
            return \false;
        }
        if (in_array($methodName, $twigMethodNames, \true)) {
            return \true;
        }
        $lowerCasedMethodName = strtolower($methodName);
        foreach ($twigMethodNames as $twigMethodName) {
            if ($lowerCasedMethodName === 'get' . strtolower($twigMethodName)) {
                return \true;
            }
            if ($lowerCasedMethodName === 'is' . strtolower($twigMethodName)) {
                return \true;
            }
            if ($lowerCasedMethodName === 'has' . strtolower($twigMethodName)) {
                return \true;
            }
        }
        return \false;
    }
}
