<?php

declare(strict_types=1);

namespace Rector\CakePHPToSymfony\Rector\Echo_;

use Nette\Utils\Html;
use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Stmt\Echo_;
use PhpParser\Node\Stmt\InlineHTML;
use Rector\CakePHPToSymfony\RouteResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;
use Symplify\SmartFileSystem\SmartFileInfo;

/**
 * @see \Rector\CakePHPToSymfony\Tests\Rector\Echo_\CakePHPTemplateLinkToTwigRector\CakePHPTemplateToTwigRectorTest
 */
final class CakePHPTemplateLinkToTwigRector extends AbstractRector
{
    /**
     * @var RouteResolver
     */
    private $routeResolver;

    public function __construct(RouteResolver $routeResolver)
    {
        $this->routeResolver = $routeResolver;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Migrate CakePHP 2.4 template method calls to Twig', [
            new CodeSample(
                <<<'PHP'
<li>
    <?php echo $this->Html->link('List Rights', ['action' => 'index']); ?>
</li>
PHP
,
                <<<'PHP'
<li>
    <a href="{{ path('index') }}">List Rights</a>
</li>
PHP

            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [Echo_::class];
    }

    /**
     * @param Echo_ $node
     */
    public function refactor(Node $node): ?Node
    {
        $firstExpression = $node->exprs[0];
        if (! $firstExpression instanceof MethodCall) {
            return null;
        }

        if (! $this->isThisHtmlPropertyFetch($firstExpression->var)) {
            return null;
        }

        $label = $firstExpression->args[0]->value;
        $parameters = $firstExpression->args[1]->value;

        # e.g. |trans https://symfony.com/doc/current/translation/templates.html#using-twig-filters
        $labelFilters = [];
        if ($label instanceof FuncCall) {
            if ($this->isName($label, '__')) {
                $labelFilters[] = 'trans';

                $label = $label->args[0]->value;
            }
        }

        $parametersValue = $this->getValue($parameters);

        /** @var SmartFileInfo|null $fileInfo */
        $fileInfo = $node->getAttribute(AttributeKey::FILE_INFO);
        assert($fileInfo instanceof SmartFileInfo);

        $routeName = $this->routeResolver->resolveFromParamsAndFileInfo($parametersValue, $fileInfo);
        $labelValue = $this->getValue($label);
        assert(is_string($labelValue));

        $aHtml = $this->createAHtml($routeName, $labelFilters, $labelValue);
        return new InlineHTML($aHtml);
    }

    private function isThisHtmlPropertyFetch(Expr $expr): bool
    {
        if (! $expr instanceof PropertyFetch) {
            return false;
        }

        if (! $this->isName($expr->var, 'this')) {
            return false;
        }

        if (! $this->isName($expr->name, 'Html')) {
            return false;
        }

        return true;
    }

    private function createAHtml(string $routeName, array $labelFilters, string $labelValue): string
    {
        $aHtml = Html::el('a');
        $aHtml->href = sprintf('{{ path(\'%s\') }}', $routeName);

        if ($labelFilters !== []) {
            $labelFilterAsString = implode('|', $labelFilters);

            $labelValue = sprintf('{{ \'%s\'|%s }}', $labelValue, $labelFilterAsString);
        }

        $aHtml->setText($labelValue);

        return $aHtml->toHtml();
    }
}
