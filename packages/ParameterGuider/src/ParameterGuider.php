<?php declare(strict_types=1);

namespace Rector\ParameterGuider;

use Nette\Utils\Strings;
use Rector\ParameterGuider\Exception\ParameterTypoException;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

/**
 * This class makes sure there are no typos in parameter names,
 * and if so, it will suggest the correct parameter name.
 */
final class ParameterGuider
{
    /**
     * @var array
     */
    private static $parametersWithMissplacedNames = [
        // correct paremter => regex catching invalid values
        'exclude_paths' => '#exclude(d)?_(path(s)?|dir(s)?|file(s)?)#'
    ];

    /**
     * @param mixed[] $parameters
     */
    public function processParameters(ParameterBagInterface $parametersBag): void
    {
        $parameterNames = array_keys($parametersBag->all());

        foreach ($parameterNames as $parameterName) {
            foreach (self::$parametersWithMissplacedNames as $correctParameterName => $missplacedNamePattern) {
                if ($parameterName === $correctParameterName) {
                    continue;
                }

                if (Strings::match($parameterName, $missplacedNamePattern)) {
                    $this->throwException($parameterName, $correctParameterName);
                }

                if (levenshtein($correctParameterName, $parameterName) < 2) {
                    $this->throwException($parameterName, $correctParameterName);
                }
            }
        }
    }

    private function throwException(string $providedParameterName, string $correctParameterName): void
    {
        throw new ParameterTypoException(sprintf(
            'Parameter "%s" does not exist. Use "%s" instead.',
            $providedParameterName,
            $correctParameterName
        ));
    }
}
