<?php

declare (strict_types=1);
namespace Rector\Symfony\Rector\MethodCall;

use RectorPrefix202305\Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Scalar\String_;
use PHPStan\Type\ObjectType;
use PHPStan\Type\ThisType;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see https://github.com/symfony/symfony/pull/21035
 * @see https://github.com/symfony/symfony/blob/4.4/src/Symfony/Bundle/FrameworkBundle/Templating/TemplateNameParser.php
 * @see https://symfony.com/doc/4.4/templates.html#bundle-templates
 *
 * @see \Rector\Symfony\Tests\Rector\ClassMethod\TemplateAnnotationToThisRenderRector\TemplateAnnotationToThisRenderRectorTest
 */
final class ConvertRenderTemplateShortNotationToBundleSyntaxRector extends AbstractRector
{
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
