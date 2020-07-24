<?php

declare(strict_types=1);

namespace Rector\SymfonyPhpConfig\Printer;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Nop;
use PhpParser\Node\Stmt\Return_;
use Rector\CodingStyle\Naming\ClassNaming;
use Rector\Core\PhpParser\Builder\ParamBuilder;
use Rector\Core\PhpParser\Builder\UseBuilder;
use Rector\Core\PhpParser\Node\NodeFactory;
use Rector\Core\PhpParser\Printer\BetterStandardPrinter;

final class ReturnClosurePrinter
{
    /**
     * @var BetterStandardPrinter
     */
    private $betterStandardPrinter;

    /**
     * @var NodeFactory
     */
    private $nodeFactory;

    /**
     * @var Node[]
     */
    private $useStmts = [];

    /**
     * @var ClassNaming
     */
    private $classNaming;

    public function __construct(
        BetterStandardPrinter $betterStandardPrinter,
        ClassNaming $classNaming,
        NodeFactory $nodeFactory
    ) {
        $this->betterStandardPrinter = $betterStandardPrinter;
        $this->nodeFactory = $nodeFactory;

        $this->classNaming = $classNaming;
    }

    public function printServices(array $services): string
    {
        // reset for each services
        $this->useStmts = [];
        $this->addUseStmts('Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator');

        $stmts = $this->createClosureStmts($services);

        $closure = new Closure([
            'params' => [$this->createClosureParam()],
            'returnType' => new Identifier('void'),
            'stmts' => $stmts,
        ]);

        $return = new Return_($closure);

        $rootStmts = array_merge($this->useStmts, [new Nop(), $return]);
        $printedContent = $this->betterStandardPrinter->prettyPrintFile($rootStmts);

        return $this->indentFluentCallToNewline($printedContent);
    }

    /**
     * @param mixed[] $services
     * @return Expression[]
     */
    private function createClosureStmts(array $services): array
    {
        $stmts = [];

        $servicesVariable = new Variable('services');

        $servicesMethodCall = new MethodCall(new Variable('containerConfiguration'), 'services');
        $assign = new Assign($servicesVariable, $servicesMethodCall);
        $stmts[] = new Expression($assign);

        foreach ($services as $serviceName => $serviceParameters) {
            $this->addUseStmts($serviceName);
            $methodCall = $this->createServicesSetMethodCall($serviceName, $servicesVariable, $serviceParameters);
            $stmts[] = new Expression($methodCall);
        }

        return $stmts;
    }

    private function createClosureParam(): Param
    {
        $paramBuilder = new ParamBuilder('containerConfigurator');
        $paramBuilder->setType('ContainerConfigurator');
        return $paramBuilder->getNode();
    }

    private function addUseStmts(string $useImport): void
    {
        $useBuilder = new UseBuilder($useImport);
        $this->useStmts[] = $useBuilder->getNode();
    }

    private function createServicesSetMethodCall(
        string $serviceName,
        Variable $servicesVariable,
        array $serviceParameters
    ): MethodCall {
        $shortClassName = $this->classNaming->getShortName($serviceName);
        $classConstFetch = new ClassConstFetch(new Name($shortClassName), new Identifier('class'));
        $args = [new Arg($classConstFetch)];

        $methodCall = new MethodCall($servicesVariable, 'set', $args);

        foreach ($serviceParameters as $argument => $value) {
            if ($this->shouldSkipObjectConfiguration($value)) {
                continue;
            }

            $args = $this->nodeFactory->createArgs([$argument, $value]);
            $methodCall = new MethodCall($methodCall, 'arg', $args);
        }

        return $methodCall;
    }

    private function indentFluentCallToNewline(string $content): string
    {
        $nextCallIndentReplacement = ')' . PHP_EOL . Strings::indent('->', 8, ' ');
        return Strings::replace($content, '#\)->#', $nextCallIndentReplacement);
    }

    private function shouldSkipObjectConfiguration($value): bool
    {
        if (! is_array($value)) {
            return false;
        }
        foreach ($value as $singleValue) {
            // new PHP configuraiton style
            if (is_object($singleValue)) {
                return true;
            }
        }

        return false;
    }
}
