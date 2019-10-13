<?php

declare(strict_types=1);

namespace Rector\Reflection;

use PHPStan\Reflection\SignatureMap\SignatureMapProvider;

final class FunctionReflectionResolver
{
    /**
     * @var SignatureMapProvider
     */
    private $signatureMapProvider;

    public function __construct(SignatureMapProvider $signatureMapProvider)
    {
        $this->signatureMapProvider = $signatureMapProvider;
    }

    public function isPhpNativeFunction(string $functionName): bool
    {
        return $this->signatureMapProvider->hasFunctionSignature($functionName);
    }
}
