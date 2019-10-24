<?php

declare(strict_types=1);

namespace Rector\ElasticSearchDSL\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
 * @see https://github.com/ongr-io/ElasticsearchDSL/blob/5.x/CHANGELOG.md
 * @see \Rector\ElasticSearchDSL\Tests\Rector\MethodCall\MigrateFilterToQueryRector\MigrateFilterToQueryRectorTest
 */
final class MigrateFilterToQueryRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Migrates addFilter to addQuery', [
            new CodeSample(
                <<<'PHP'
use ONGR\ElasticsearchDSL\Search;
use ONGR\ElasticsearchDSL\Query\TermsQuery;

class SomeClass
{
    public function run()
    {
        $search = new Search();

        $search->addFilter(
            new TermsQuery('categoryIds', [1, 2])
        );
    }
}
PHP
                ,
                <<<'PHP'
use ONGR\ElasticsearchDSL\Search;
use ONGR\ElasticsearchDSL\Query\TermsQuery;
use ONGR\ElasticsearchDSL\Query\Compound\BoolQuery;

class SomeClass
{
    public function run()
    {
        $search = new Search();

        $search->addQuery(
            new TermsQuery('categoryIds', [1, 2]),
            BoolQuery::FILTER
        );
    }
}
PHP
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
        if (! $this->isObjectType($node, 'ONGR\ElasticsearchDSL\Search')) {
            return null;
        }

        if (! $this->isName($node->name, 'addFilter')) {
            return null;
        }

        $node->name = new Identifier('addQuery');

        $classConstFetch = $this->createClassConstant('ONGR\ElasticsearchDSL\Query\Compound\BoolQuery', 'FILTER');

        $node->args[1] = new Arg($classConstFetch);

        return $node;
    }
}
