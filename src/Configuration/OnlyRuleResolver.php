<?php

declare(strict_types=1);

namespace Rector\Core\Configuration;

use Nette\Utils\Strings;
use Rector\Core\Exception\Configuration\RectorRuleNotFoundException;

/**
 * @see \Rector\Core\Tests\Configuration\OnlyRuleResolverTest
 */
final class OnlyRuleResolver
{
    /**
     * @var string
     * @see https://regex101.com/r/WOQuBL/1
     */
    private const SLASH_REGEX = '#\\\\#';

    /**
     * @var RectorClassesProvider
     */
    private $rectorClassesProvider;

    public function __construct(RectorClassesProvider $rectorClassesProvider)
    {
        $this->rectorClassesProvider = $rectorClassesProvider;
    }

    public function resolve(string $rule): string
    {
        $rule = ltrim($rule, '\\');

        $rectorClasses = $this->rectorClassesProvider->provide();

        // 1. exact class
        foreach ($rectorClasses as $rectorClass) {
            if (is_a($rectorClass, $rule, true)) {
                return $rule;
            }
        }

        // 2. short class
        foreach ($rectorClasses as $rectorClass) {
            if (Strings::endsWith($rectorClass, '\\' . $rule)) {
                return $rectorClass;
            }
        }

        // 3. class without slashes
        foreach ($rectorClasses as $rectorClass) {
            $rectorClassWithoutSlashes = Strings::replace($rectorClass, self::SLASH_REGEX);
            if ($rectorClassWithoutSlashes === $rule) {
                return $rectorClass;
            }
        }

        $message = sprintf(
            'Rule "%s" was not found.%sMake sure it is registered in your config or in one of the sets',
            $rule,
            PHP_EOL
        );
        throw new RectorRuleNotFoundException($message);
    }
}
