<?php declare(strict_types=1);

namespace Rector\ContributorTools\Command;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Const_;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\AssignOp;
use PhpParser\Node\Expr\AssignRef;
use PhpParser\Node\Expr\BinaryOp;
use PhpParser\Node\Expr\BitwiseNot;
use PhpParser\Node\Expr\BooleanNot;
use PhpParser\Node\Expr\Cast;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\Clone_;
use PhpParser\Node\Expr\ClosureUse;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\Empty_;
use PhpParser\Node\Expr\Error;
use PhpParser\Node\Expr\ErrorSuppress;
use PhpParser\Node\Expr\Eval_;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Include_;
use PhpParser\Node\Expr\Instanceof_;
use PhpParser\Node\Expr\Isset_;
use PhpParser\Node\Expr\List_;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\PostDec;
use PhpParser\Node\Expr\PostInc;
use PhpParser\Node\Expr\PreDec;
use PhpParser\Node\Expr\PreInc;
use PhpParser\Node\Expr\Print_;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\ShellExec;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\StaticPropertyFetch;
use PhpParser\Node\Expr\Ternary;
use PhpParser\Node\Expr\UnaryMinus;
use PhpParser\Node\Expr\UnaryPlus;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Expr\YieldFrom;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\NullableType;
use PhpParser\Node\Param;
use PhpParser\Node\Scalar\DNumber;
use PhpParser\Node\Scalar\Encapsed;
use PhpParser\Node\Scalar\EncapsedStringPart;
use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Case_;
use PhpParser\Node\Stmt\Catch_;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassConst;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Const_ as ConstStmt;
use PhpParser\Node\Stmt\Declare_;
use PhpParser\Node\Stmt\DeclareDeclare;
use PhpParser\Node\Stmt\Do_;
use PhpParser\Node\Stmt\Echo_;
use PhpParser\Node\Stmt\ElseIf_;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Foreach_;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\Global_;
use PhpParser\Node\Stmt\Goto_;
use PhpParser\Node\Stmt\GroupUse;
use PhpParser\Node\Stmt\HaltCompiler;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\InlineHTML;
use PhpParser\Node\Stmt\Interface_;
use PhpParser\Node\Stmt\Label;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\Stmt\PropertyProperty;
use PhpParser\Node\Stmt\Static_;
use PhpParser\Node\Stmt\StaticVar;
use PhpParser\Node\Stmt\Switch_;
use PhpParser\Node\Stmt\Throw_;
use PhpParser\Node\Stmt\Trait_;
use PhpParser\Node\Stmt\TraitUse;
use PhpParser\Node\Stmt\TraitUseAdaptation\Alias;
use PhpParser\Node\Stmt\TraitUseAdaptation\Precedence;
use PhpParser\Node\Stmt\TryCatch;
use PhpParser\Node\Stmt\Unset_;
use PhpParser\Node\Stmt\Use_;
use PhpParser\Node\Stmt\UseUse;
use PhpParser\Node\Stmt\While_;
use PhpParser\Node\VarLikeIdentifier;
use Rector\Console\Command\AbstractCommand;
use Rector\Console\Shell;
use Rector\ContributorTools\Contract\OutputFormatter\DumpNodesOutputFormatterInterface;
use Rector\ContributorTools\Node\NodeClassProvider;
use Rector\ContributorTools\Node\NodeInfo;
use Rector\ContributorTools\Node\NodeInfoResult;
use Rector\Exception\ShouldNotHappenException;
use Rector\PhpParser\Printer\BetterStandardPrinter;
use ReflectionClass;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symplify\PackageBuilder\Console\Command\CommandNaming;

final class DumpNodesCommand extends AbstractCommand
{
    /**
     * @var BetterStandardPrinter
     */
    private $betterStandardPrinter;

    /**
     * @var NodeClassProvider
     */
    private $nodeClassProvider;

    /**
     * @var NodeInfoResult
     */
    private $nodeInfoResult;

    /**
     * @var DumpNodesOutputFormatterInterface[]
     */
    private $outputFormatters = [];

    /**
     * @param DumpNodesOutputFormatterInterface[] $outputFormatters
     */
    public function __construct(
        BetterStandardPrinter $betterStandardPrinter,
        NodeClassProvider $nodeClassProvider,
        NodeInfoResult $nodeInfoResult,
        array $outputFormatters
    ) {
        $this->betterStandardPrinter = $betterStandardPrinter;
        $this->nodeClassProvider = $nodeClassProvider;
        $this->nodeInfoResult = $nodeInfoResult;
        $this->outputFormatters = $outputFormatters;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName(CommandNaming::classToName(self::class));
        $this->setDescription('Dump overview of all Nodes');
        $this->addOption(
            'output-format',
            'o',
            InputOption::VALUE_REQUIRED,
            'Output format for Nodes [json, console]',
            'console'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $nodeClasses = $this->nodeClassProvider->getNodeClasses();

        foreach ($nodeClasses as $nodeClass) {
            if ($nodeClass === Error::class) {
                continue;
            }

            $nodeClassReflection = new ReflectionClass($nodeClass);
            if ($nodeClassReflection->isAbstract()) {
                continue;
            }

            /** @var Node $node */
            $contructorReflection = $nodeClassReflection->getConstructor();

            if ($contructorReflection->getNumberOfRequiredParameters() === 0) {
                $node = $nodeClassReflection->newInstance();
                $category = $this->resolveCategoryByNodeClass($nodeClass);
                $this->nodeInfoResult->addNodeInfo($category, new NodeInfo(
                    $nodeClass,
                    $this->betterStandardPrinter->print($node),
                    false
                ));
            } elseif (is_a($nodeClass, BinaryOp::class, true)) {
                $node = new $nodeClass(new LNumber(1), new String_('a'));
                $this->nodeInfoResult->addNodeInfo(BinaryOp::class, new NodeInfo(
                    $nodeClass,
                    $this->betterStandardPrinter->print($node),
                    true
                ));
            } elseif (is_a($nodeClass, AssignOp::class, true)) {
                $node = new $nodeClass(new Variable('variable'), new String_('value'));
                $this->nodeInfoResult->addNodeInfo(AssignOp::class, new NodeInfo(
                    $nodeClass,
                    $this->betterStandardPrinter->print($node),
                    true
                ));
            } elseif (is_a($nodeClass, Cast::class, true)) {
                $node = new $nodeClass(new Variable('value'));
                $this->nodeInfoResult->addNodeInfo(Cast::class, new NodeInfo(
                    $nodeClass,
                    $this->betterStandardPrinter->print($node),
                    true
                ));
            } elseif (is_a($nodeClass, Name::class, true)) {
                $node = new $nodeClass('name');
                $this->nodeInfoResult->addNodeInfo(Name::class, new NodeInfo(
                    $nodeClass,
                    $this->betterStandardPrinter->print($node),
                    true
                ));
            } else {
                if ($nodeClass === VarLikeIdentifier::class) {
                    continue;
                }

                $useUseNode = new UseUse(new Name('UsedNamespace'));
                $someVariableNode = new Variable('someVariable');

                if ($nodeClass === NullableType::class) {
                    $node = new NullableType('SomeType');
                } elseif ($nodeClass === Const_::class) {
                    $node = new Const_('CONSTANT_NAME', new String_('default'));
                } elseif ($nodeClass === Identifier::class) {
                    $node = new Identifier('identifier');
                } elseif ($nodeClass === DNumber::class) {
                    $node = new DNumber(10.5);
                } elseif ($nodeClass === String_::class) {
                    $node = new String_('string');
                } elseif ($nodeClass === Encapsed::class) {
                    $node = new Encapsed([new Variable('enscapsed')]);
                } elseif ($nodeClass === LNumber::class) {
                    $node = new LNumber(100);
                } elseif ($nodeClass === EncapsedStringPart::class) {
                    $node = new EncapsedStringPart('enscapsed');
                } elseif ($nodeClass === DeclareDeclare::class) {
                    $node = new DeclareDeclare('strict_types', new LNumber(1));
                } elseif ($nodeClass === Do_::class) {
                    $node = new Do_(new Variable('variable'));
                } elseif ($nodeClass === Static_::class) {
                    $node = new Static_([new Variable('static')]);
                } elseif ($nodeClass === TraitUse::class) {
                    $node = new TraitUse([new Name('trait')]);
                } elseif ($nodeClass === Switch_::class) {
                    $node = new Switch_(new Variable('variable'), [new Case_(new LNumber(1))]);
                } elseif ($nodeClass === Echo_::class) {
                    $node = new Echo_([new String_('hello')]);
                } elseif ($nodeClass === StaticVar::class) {
                    $node = new StaticVar(new Variable('variable'));
                } elseif ($nodeClass === Property::class) {
                    $node = new Property(0, [new PropertyProperty('property')]);
                } elseif ($nodeClass === Unset_::class) {
                    $node = new Unset_([new Variable('variable')]);
                } elseif ($nodeClass === Label::class) {
                    $node = new Label('label');
                } elseif ($nodeClass === If_::class) {
                    $node = new If_(new ConstFetch(new Name('true')));
                } elseif ($nodeClass === Function_::class) {
                    $node = new Function_('some_function');
                } elseif ($nodeClass === ClassMethod::class) {
                    $node = new ClassMethod('someMethod');
                } elseif ($nodeClass === Case_::class) {
                    $node = new Case_(new ConstFetch(new Name('true')));
                } elseif ($nodeClass === Use_::class) {
                    $node = new Use_([$useUseNode]);
                } elseif ($nodeClass === Foreach_::class) {
                    $node = new Foreach_(new Variable('variables'), new Variable('value'));
                } elseif ($nodeClass === Catch_::class) {
                    $node = new Catch_([new Name('CatchedType')], new Variable('catchedVariable'));
                } elseif ($nodeClass === ConstStmt::class) {
                    $node = new ConstStmt([new Const_('CONSTANT_IN_CLASS', new String_('default value'))]);
                } elseif ($nodeClass === Declare_::class) {
                    $node = new Declare_([new DeclareDeclare('strict_types', new LNumber(1))]);
                } elseif ($nodeClass === ClassConst::class) {
                    $node = new ClassConst([new Const_('SOME_CLASS_CONSTANT', new String_('default value'))]);
                } elseif ($nodeClass === GroupUse::class) {
                    $node = new GroupUse(new Name('prefix'), [$useUseNode]);
                } elseif ($nodeClass === InlineHTML::class) {
                    $node = new InlineHTML('<strong>feel</strong>');
                } elseif ($nodeClass === UseUse::class) {
                    $node = $useUseNode;
                } elseif ($nodeClass === Expression::class) {
                    $node = new Expression(new Variable('someVariable'));
                } elseif ($nodeClass === PropertyProperty::class) {
                    $node = new PropertyProperty('someProperty');
                } elseif ($nodeClass === Global_::class) {
                    $node = new Global_([new Variable('globalVariable')]);
                } elseif ($nodeClass === Precedence::class) {
                    $node = new Precedence(new Name('SomeTrait'), 'someMethod', [new Name('overriddenTrait')]);
                } elseif ($nodeClass === Alias::class) {
                    $node = new Alias(new Name('SomeTrait'), 'method', Class_::MODIFIER_PUBLIC, 'aliasedMethod');
                } elseif ($nodeClass === Throw_::class) {
                    $node = new Throw_(new New_(new Variable('someException')));
                } elseif ($nodeClass === TryCatch::class) {
                    $node = new TryCatch([new Function_('someFunction')], [new Function_('logException')]);
                } elseif ($nodeClass === Interface_::class) {
                    $node = new Interface_(new Name('SomeInterface'));
                } elseif ($nodeClass === ElseIf_::class) {
                    $node = new ElseIf_(new ConstFetch(new Name('true')));
                } elseif ($nodeClass === Goto_::class) {
                    $node = new Goto_('goto_break');
                } elseif ($nodeClass === HaltCompiler::class) {
                    $node = new HaltCompiler('remaining');
                } elseif ($nodeClass === Trait_::class) {
                    $node = new Trait_('TraitName');
                } elseif ($nodeClass === While_::class) {
                    $node = new While_(new Variable('variable'));
                } elseif ($nodeClass === Class_::class) {
                    $node = new Class_(new Name('ClassName'));
                } elseif ($nodeClass === PostDec::class) {
                    $node = new PostDec(new Variable('someVariable'));
                } elseif ($nodeClass === PreInc::class) {
                    $node = new PreInc(new Variable('someVariable'));
                } elseif ($nodeClass === PostInc::class) {
                    $node = new PostInc(new Variable('someVariable'));
                } elseif ($nodeClass === PreDec::class) {
                    $node = new PreDec(new Variable('someVariable'));
                } elseif ($nodeClass === List_::class) {
                    $node = new List_([new ArrayItem(new Variable('someVariable'))]);
                } elseif ($nodeClass === Instanceof_::class) {
                    $node = new Instanceof_($someVariableNode, new Name('SomeClass'));
                } elseif ($nodeClass === FuncCall::class) {
                    $node = new FuncCall(new Name('functionCall'));
                } elseif ($nodeClass === Empty_::class) {
                    $node = new Empty_($someVariableNode);
                } elseif ($nodeClass === StaticCall::class) {
                    $node = new StaticCall(new Name('SomeClass'), 'methodName');
                } elseif ($nodeClass === MethodCall::class) {
                    $node = new MethodCall(new Variable('someObject'), 'methodName');
                } elseif ($nodeClass === ShellExec::class) {
                    $node = new ShellExec([new EncapsedStringPart('encapsed'), new EncapsedStringPart('string')]);
                } elseif ($nodeClass === New_::class) {
                    $node = new New_(new Class_('SomeClass'));
                } elseif ($nodeClass === PropertyFetch::class) {
                    $node = new PropertyFetch($someVariableNode, 'propertyName');
                } elseif ($nodeClass === Isset_::class) {
                    $node = new Isset_([new Variable('variable')]);
                } elseif ($nodeClass === ArrayDimFetch::class) {
                    $node = new ArrayDimFetch($someVariableNode, new LNumber(0));
                } elseif ($nodeClass === Print_::class) {
                    $node = new Print_($someVariableNode);
                } elseif ($nodeClass === Include_::class) {
                    // contains: include_once, require, require_once
                    $node = new Include_($someVariableNode, Include_::TYPE_INCLUDE);
                } elseif ($nodeClass === Clone_::class) {
                    $node = new Clone_($someVariableNode);
                } elseif ($nodeClass === ArrayItem::class) {
                    $node = new ArrayItem(new Variable('Tom'), new String_('name'));
                } elseif ($nodeClass === UnaryMinus::class) {
                    $node = new UnaryMinus($someVariableNode);
                } elseif ($nodeClass === StaticPropertyFetch::class) {
                    $node = new StaticPropertyFetch(new Name('SomeClass'), new VarLikeIdentifier('someProperty'));
                } elseif ($nodeClass === Variable::class) {
                    $node = $someVariableNode;
                } elseif ($nodeClass === ClosureUse::class) {
                    $node = new ClosureUse($someVariableNode);
                } elseif ($nodeClass === AssignRef::class) {
                    $node = new AssignRef($someVariableNode, new Variable('someOtherVariable'));
                } elseif ($nodeClass === ErrorSuppress::class) {
                    $node = new ErrorSuppress($someVariableNode);
                } elseif ($nodeClass === Eval_::class) {
                    $node = new Eval_(new String_('Some php code'));
                } elseif ($nodeClass === BooleanNot::class) {
                    $node = new BooleanNot(new ConstFetch(new Name('true')));
                } elseif ($nodeClass === YieldFrom::class) {
                    $node = new YieldFrom($someVariableNode);
                } elseif ($nodeClass === ConstFetch::class) {
                    $node = new ConstFetch(new Name('true'));
                } elseif ($nodeClass === Assign::class) {
                    $node = new Assign($someVariableNode, new String_('some value'));
                } elseif ($nodeClass === BitwiseNot::class) {
                    $node = new BitwiseNot($someVariableNode);
                } elseif ($nodeClass === Ternary::class) {
                    $node = new Ternary($someVariableNode, new ConstFetch(new Name('true')), new ConstFetch(new Name(
                        'false'
                    )));
                } elseif ($nodeClass === UnaryPlus::class) {
                    $node = new UnaryPlus($someVariableNode);
                } elseif ($nodeClass === UnaryMinus::class) {
                    $node = new UnaryMinus($someVariableNode);
                } elseif ($nodeClass === ClassConstFetch::class) {
                    $node = new ClassConstFetch(new Name('SomeClass'), new Identifier('SOME_CONSTANT'));
                } elseif ($nodeClass === Param::class) {
                    $node = new Param($someVariableNode);
                } elseif ($nodeClass === Arg::class) {
                    $node = new Arg($someVariableNode);
                } else {
                    throw new ShouldNotHappenException(sprintf(
                        'Implement a new printer for "%s" node in "%s"',
                        $nodeClass,
                        __METHOD__
                    ));
                }

                $category = $this->resolveCategoryByNodeClass($nodeClass);

                $this->nodeInfoResult->addNodeInfo($category, new NodeInfo(
                    $nodeClass,
                    $this->betterStandardPrinter->print($node),
                    true
                ));

                // @todo print also the node structure, how it was made! basically 1:1 how it was created here; so people have the idea what everything they can/need put in it
                // @todo maybe show naked + full use + printed version :)
            }
        }

        foreach ($this->outputFormatters as $outputFormatter) {
            if ($outputFormatter->getName() !== $input->getOption('output-format')) {
                continue;
            }

            $outputFormatter->format($this->nodeInfoResult);
        }

        return Shell::CODE_SUCCESS;
    }

    private function resolveCategoryByNodeClass(string $nodeClass): string
    {
        if (Strings::contains($nodeClass, '\\Scalar\\')) {
            return 'Scalar nodes';
        }

        if (Strings::contains($nodeClass, '\\Expr\\')) {
            return 'Expressions';
        }

        if (Strings::contains($nodeClass, '\\Stmt\\')) {
            return 'Statements';
        }

        return 'Various';
    }
}
