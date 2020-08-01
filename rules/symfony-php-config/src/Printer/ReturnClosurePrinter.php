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
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\PhpParser\Builder\ParamBuilder;
use Rector\Core\PhpParser\Builder\UseBuilder;
use Rector\Core\PhpParser\Node\NodeFactory;
use Rector\Core\PhpParser\Printer\BetterStandardPrinter;
use ReflectionClass;

final class ReturnClosurePrinter
{
    /**
     * @var string
     */
    private const CONTAINER_CONFIGURATOR = 'containerConfigurator';

    /**
     * @var Node[]
     */
    private $useStmts = [];

    /**
     * @var BetterStandardPrinter
     */
    private $betterStandardPrinter;

    /**
     * @var NodeFactory
     */
    private $nodeFactory;

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

        $closure = new Closure([
            'params' => [$this->createClosureParam()],
            'returnType' => new Identifier('void'),
            'stmts' => $this->createClosureStmts($services),
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

        $servicesMethodCall = new MethodCall(new Variable(self::CONTAINER_CONFIGURATOR), 'services');
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
        $paramBuilder = new ParamBuilder(self::CONTAINER_CONFIGURATOR);
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

            if (! is_string($argument)) {
                $message = sprintf('Invalid configuration for code sample in "%s" class', $serviceName);
                throw new ShouldNotHappenException($message);
            }

            $constantName = $this->resolveClassConstantNameFromValue($argument, $serviceName);
            $classConstFetch = new ClassConstFetch(new Name($shortClassName), $constantName);

            $args = $this->nodeFactory->createArgs(['configure', [[$classConstFetch, $value]]]);
            $methodCall = new MethodCall($methodCall, 'call', $args);
        }

        return $methodCall;
    }

    private function indentFluentCallToNewline(string $content): string
    {
        $nextCallIndentReplacement = ')' . PHP_EOL . Strings::indent('->', 8, ' ');
        return Strings::replace($content, '#\)->#', $nextCallIndentReplacement);
    }

    /**
     * @param mixed[]|mixed $value
     */
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

    private function resolveClassConstantNameFromValue(string $constantValue, string $class): string
    {
        $reflectionClass = new ReflectionClass($class);
        foreach ($reflectionClass->getConstants() as $name => $value) {
            if ($value === $constantValue) {
                return $name;
            }
        }

        $message = sprintf(
            'Constant value "%s" for class "%s" could not be resolved. Make sure you use constants references there, not string values',
            $constantValue,
            $class
        );
        throw new ShouldNotHappenException($message);
    }
}
