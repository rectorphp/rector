<?php declare(strict_types=1);

namespace Rector\DependencyInjection\CompilerPass;

use Nette\Utils\Strings;
use Symfony\Component\DependencyInjection\Argument\BoundArgument;
use Symfony\Component\DependencyInjection\Compiler\AbstractRecursivePass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

/**
 * Bind parameters by default:
 * - from "%value_name%"
 * - to "$valueName"
 */
final class AutoBindParametersCompilerPass extends AbstractRecursivePass
{
    public function process(ContainerBuilder $containerBuilder): void
    {
        $boundArguments = $this->createBoundArgumentsFromParameterBag($containerBuilder->getParameterBag());

        foreach ($containerBuilder->getDefinitions() as $definition) {
            // config binding has priority over default one
            $bindings = array_merge($definition->getBindings(), $boundArguments);
            $definition->setBindings($bindings);
        }
    }

    /**
     * @return BoundArgument[]
     */
    private function createBoundArgumentsFromParameterBag(ParameterBagInterface $parameterBag): array
    {
        $boundArguments = [];
        foreach ($parameterBag->all() as $name => $value) {
            // skip system
            if (Strings::startsWith($name, 'kernel.')) {
                continue;
            }

            $parameterGuess = '$' . $this->undescoredToCamelCase($name);

            $boundArgument = new BoundArgument($value);

            // set used so it doesn't end on exceptions
            [$value, $identifier, $isUsed] = $boundArgument->getValues();
            $boundArgument->setValues([$value, $identifier, true]);

            $boundArguments[$parameterGuess] = $boundArgument;
        }

        return $boundArguments;
    }

    /**
     * @see https://stackoverflow.com/a/2792045/1348344
     */
    private function undescoredToCamelCase(string $string): string
    {
        $string = str_replace('_', '', ucwords($string, '_'));

        return lcfirst($string);
    }
}
