<?php

declare(strict_types=1);

namespace Rector\DependencyInjection\TypeAnalyzer;

use Nette\Utils\Strings;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagValueNode;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\JMS\JMSInjectTagValueNode;

final class InjectParameterAnalyzer
{
    /**
     * @var string
     * @see https://regex101.com/r/pjusUN/1
     */
    private const BETWEEN_PERCENT_CHARS_REGEX = '#%(.*?)%#';

    public function isParameterInject(PhpDocTagValueNode $phpDocTagValueNode): bool
    {
        if (! $phpDocTagValueNode instanceof JMSInjectTagValueNode) {
            return false;
        }

        $serviceName = $phpDocTagValueNode->getServiceName();
        if ($serviceName === null) {
            return false;
        }

        return (bool) Strings::match($serviceName, self::BETWEEN_PERCENT_CHARS_REGEX);
    }
}
