<?php

declare (strict_types=1);
namespace Rector\Symfony\Symfony43\Rector\MethodCall;

use RectorPrefix202407\Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Scalar\String_;
use PHPStan\Type\ObjectType;
use PHPStan\Type\ThisType;
use Rector\PhpParser\Node\Value\ValueResolver;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://github.com/symfony/symfony/pull/21035
 * @changelog https://github.com/symfony/symfony/blob/4.4/src/Symfony/Bundle/FrameworkBundle/Templating/TemplateNameParser.php
 * @changelog https://symfony.com/doc/4.4/templates.html#bundle-templates
 *
 * @see \Rector\Symfony\Tests\Symfony43\Rector\MethodCall\ConvertRenderTemplateShortNotationToBundleSyntaxRector\ConvertRenderTemplateShortNotationToBundleSyntaxRectorTest
 */
final class ConvertRenderTemplateShortNotationToBundleSyntaxRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\PhpParser\Node\Value\ValueResolver
     */
    private $valueResolver;
    public function __construct(ValueResolver $valueResolver)
    {
        $this->valueResolver = $valueResolver;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Change Twig template short name to bundle syntax in render calls from controllers', [new CodeSample(<<<'CODE_SAMPLE'
class BaseController extends Controller {
    function indexAction()
    {
        $this->render('appBundle:Landing\Main:index.html.twig');
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class BaseController extends Controller {
    function indexAction()
    {
        $this->render('@app/Landing/Main/index.html.twig');
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [MethodCall::class];
    }
    /**
     * @param MethodCall $node
     */
    public function refactor(Node $node) : ?Node
    {
        if (!$this->nodeNameResolver->isName($node->name, 'render') && !$this->nodeNameResolver->isName($node->name, 'renderView')) {
            return null;
        }
        $objectType = $this->nodeTypeResolver->getType($node->var);
        $controllerType = new ObjectType('Symfony\\Bundle\\FrameworkBundle\\Controller\\Controller');
        if (!$controllerType->isSuperTypeOf($objectType)->yes()) {
            return null;
        }
        if (!$objectType instanceof ThisType) {
            return null;
        }
        $args = $node->getArgs();
        if (!isset($args[0])) {
            return null;
        }
        $tplName = $this->valueResolver->getValue($args[0]->value);
        if ($tplName === null) {
            return null;
        }
        $matches = Strings::match($tplName, '/:/', \PREG_OFFSET_CAPTURE);
        if ($matches === null) {
            return null;
        }
        $newValue = '@' . Strings::replace(\substr((string) $tplName, 0, $matches[0][1]), '/Bundle/', '') . Strings::replace(\substr((string) $tplName, $matches[0][1]), '/:/', '/');
        $newValue = \str_replace('\\', '/', $newValue);
        $node->args[0] = new Arg(new String_($newValue));
        return $node;
    }
}
