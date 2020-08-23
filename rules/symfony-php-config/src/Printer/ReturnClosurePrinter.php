<?php

declare(strict_types=1);

namespace Rector\SymfonyPhpConfig\Printer;

use Nette\Utils\Strings;
use PhpParser\BuilderHelpers;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
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
use Rector\Core\Reflection\ConstantNameFromValueResolver;
use Rector\SymfonyPhpConfig\NodeFactory\NewValueObjectFactory;

/**
 * @deprecated
 * @todo use migrify package https://github.com/migrify/php-config-printer
 */
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

    /**
     * @var ConstantNameFromValueResolver
     */
    private $constantNameFromValueResolver;

    /**
     * @var NewValueObjectFactory
     */
    private $newValueObjectFactory;

    public function __construct(
        BetterStandardPrinter $betterStandardPrinter,
        ClassNaming $classNaming,
        NodeFactory $nodeFactory,
        ConstantNameFromValueResolver $constantNameFromValueResolver,
        NewValueObjectFactory $newValueObjectFactory
    ) {
        $this->betterStandardPrinter = $betterStandardPrinter;
        $this->nodeFactory = $nodeFactory;
        $this->classNaming = $classNaming;
        $this->constantNameFromValueResolver = $constantNameFromValueResolver;
        $this->newValueObjectFactory = $newValueObjectFactory;
    }

    /**
     * @todo use migrify package
     */
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

        $printedContent = $this->indentArray($printedContent);

        return $this->indentFluentCallToNewline($printedContent);
    }

    private function addUseStmts(string $useImport): void
    {
        $useBuilder = new UseBuilder($useImport);
        $this->useStmts[] = $useBuilder->getNode();
    }

    private function createClosureParam(): Param
    {
        $paramBuilder = new ParamBuilder(self::CONTAINER_CONFIGURATOR);
        $paramBuilder->setType('ContainerConfigurator');
        return $paramBuilder->getNode();
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

    /**
     * @todo replace with https://github.com/symplify/symplify/issues/2055 when done
     */
    private function indentArray(string $printedContent): string
    {
        // open array
        $printedContent = Strings::replace($printedContent, '#\[\[#', '[[' . PHP_EOL . str_repeat(' ', 12));

        // nested array
        $printedContent = Strings::replace($printedContent, '#\=> \[#', '=> [' . PHP_EOL . str_repeat(' ', 16));

        // close array
        $printedContent = Strings::replace($printedContent, '#\]\]\)#', PHP_EOL . str_repeat(' ', 8) . ']])');

        return $printedContent;
    }

    private function indentFluentCallToNewline(string $content): string
    {
        $nextCallIndentReplacement = ')' . PHP_EOL . Strings::indent('->', 8, ' ');
        return Strings::replace($content, '#\)->#', $nextCallIndentReplacement);
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
            if (! is_string($argument)) {
                $message = sprintf('Invalid configuration for code sample in "%s" class', $serviceName);
                throw new ShouldNotHappenException($message);
            }

            $constantName = $this->constantNameFromValueResolver->resolveFromValueAndClass($argument, $serviceName);
            $classConstFetch = new ClassConstFetch(new Name($shortClassName), $constantName);

            $args = $this->nodeFactory->createArgs(
                ['configure', [[$this->createConfigureArgs($value, $classConstFetch)]]]
            );

            $methodCall = new MethodCall($methodCall, 'call', $args);
        }

        return $methodCall;
    }

    /**
     * @param mixed|mixed[] $value
     */
    private function createConfigureArgs($value, ClassConstFetch $classConstFetch): ArrayItem
    {
        if (is_array($value)) {
            foreach ($value as $key => $subValue) {
                // PHP object configuration
                if (is_object($subValue)) {
                    $new = $this->newValueObjectFactory->create($subValue);
                    $args = [new Arg($new)];

                    // create arguments from object properties
                    $inlineObjectFuncCall = new FuncCall(new FullyQualified(
                        'Rector\SymfonyPhpConfig\inline_value_object'
                    ), $args);
                    $value[$key] = $inlineObjectFuncCall;
                }
            }
        }

        return new ArrayItem(BuilderHelpers::normalizeValue($value), $classConstFetch);
    }
}
