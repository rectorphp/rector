<?php

declare (strict_types=1);
namespace Rector\CodeQuality\NodeManipulator;

use PhpParser\Node\Arg;
use PhpParser\Node\Identifier;
use PHPStan\Reflection\FunctionReflection;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\ParametersAcceptorSelector;
final class NamedArgsSorter
{
    /**
     * @param Arg[] $currentArgs
     * @return list<Arg>
     * @param \PHPStan\Reflection\FunctionReflection|\PHPStan\Reflection\MethodReflection $functionLikeReflection
     */
    public function sortArgsToMatchReflectionParameters(array $currentArgs, $functionLikeReflection): array
    {
        $extendedParametersAcceptor = ParametersAcceptorSelector::combineAcceptors($functionLikeReflection->getVariants());
        $parameters = $extendedParametersAcceptor->getParameters();
        $order = [];
        foreach ($parameters as $key => $parameter) {
            $order[$parameter->getName()] = $key;
        }
        $sortedArgs = [];
        $toSortArgs = [];
        foreach ($currentArgs as $currentArg) {
            if (!$currentArg->name instanceof Identifier) {
                $sortedArgs[] = $currentArg;
                continue;
            }
            $toSortArgs[] = $currentArg;
        }
        usort($toSortArgs, static function (Arg $arg1, Arg $arg2) use ($order): int {
            /** @var Identifier $argName1 */
            $argName1 = $arg1->name;
            /** @var Identifier $argName2 */
            $argName2 = $arg2->name;
            $order1 = $order[$argName1->name] ?? \PHP_INT_MAX;
            $order2 = $order[$argName2->name] ?? \PHP_INT_MAX;
            return $order1 <=> $order2;
        });
        return array_merge($sortedArgs, $toSortArgs);
    }
}
