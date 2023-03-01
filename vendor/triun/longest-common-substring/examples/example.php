<?php

namespace RectorPrefix202303;

require \dirname(__DIR__) . '/vendor/autoload.php';
use RectorPrefix202303\Triun\LongestCommonSubstring\Solver;
use RectorPrefix202303\Triun\LongestCommonSubstring\MatchesSolver;
use RectorPrefix202303\Symfony\Component\VarDumper\Cloner\VarCloner;
use RectorPrefix202303\Symfony\Component\VarDumper\Dumper\CliDumper;
use RectorPrefix202303\Symfony\Component\VarDumper\Dumper\HtmlDumper;
function dump($value)
{
    if (\class_exists(CliDumper::class)) {
        $dumper = 'cli' === \PHP_SAPI ? new CliDumper() : new HtmlDumper();
        $dumper->dump((new VarCloner())->cloneVar($value));
    } else {
        \var_dump($value);
    }
}
$solver = new Solver();
$matchSolver = new MatchesSolver();
$stringA = '0123456789ABCDEF0123456789ABCDEF0123456789ABCDEF0123456789ABCDEF';
$stringB = '56789AB56789ABCDE56789ABCDE56789AB56789A123456789A';
$matches = $matchSolver->solve($stringA, $stringB);
$result = $solver->solve($stringA, $stringB);
dump($stringA);
dump($stringB);
dump($matches);
dump($matches->toArray());
dump($matches->toJson());
dump($matches->values());
dump($matches->unique());
dump((string) $matches[0]);
dump("{$matches[0]}");
dump('' . $matches[0]);
dump($matches->firstValue());
dump((string) $matches->first());
dump((string) $matches);
dump($result);
