<?php declare(strict_types=1);

namespace Rector\ElasticSearchDSL\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name\FullyQualified;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
 * @see https://github.com/ongr-io/ElasticsearchDSL/blob/5.x/CHANGELOG.md
 */
final class MigrateFilterToQueryRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Migrates addFilter to addQuery', [
            new CodeSample(
                <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $search = new \ONGR\ElasticsearchDSL\Search();

        $search->addFilter(
            new \ONGR\ElasticsearchDSL\Query\TermsQuery('categoryIds', [1, 2])
        );
    }
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $search = new \ONGR\ElasticsearchDSL\Search();

        $search->addQuery(
            new \ONGR\ElasticsearchDSL\Query\TermsQuery('categoryIds', [1, 2]),
            \ONGR\ElasticsearchDSL\Query\Compound\BoolQuery::FILTER
        );
    }
}
CODE_SAMPLE
            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [MethodCall::class];
    }

    /**
     * @param MethodCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $this->isType($node, 'ONGR\ElasticsearchDSL\Search')) {
            return null;
        }

        if (! $this->isName($node, 'addFilter')) {
            return null;
        }

        $node->name = new Identifier('addQuery');

        $node->args[1] = new Arg(new ClassConstFetch(new FullyQualified(
            'ONGR\ElasticsearchDSL\Query\Compound\BoolQuery'
        ), new Identifier('FILTER')));

        return $node;
    }
}
