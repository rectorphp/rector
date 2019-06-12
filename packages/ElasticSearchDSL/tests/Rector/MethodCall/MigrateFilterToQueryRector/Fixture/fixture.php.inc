<?php

namespace Rector\ElasticSearchDSL\Tests\Rector\MethodCall\MigrateFilterToQueryRector\Fixture;

class SomeClass
{
    public function run()
    {
        $search = new \ONGR\ElasticsearchDSL\Search();

        $search->addFilter(
            new \ONGR\ElasticsearchDSL\Query\TermsQuery('categoryIds', [1, 2])
        );

        $search->addQuery(
            new \ONGR\ElasticsearchDSL\Query\TermsQuery('categoryIds', [1, 2]), \ONGR\ElasticsearchDSL\Query\Compound\BoolQuery::FILTER
        );
    }
}

?>
-----
<?php

namespace Rector\ElasticSearchDSL\Tests\Rector\MethodCall\MigrateFilterToQueryRector\Fixture;

class SomeClass
{
    public function run()
    {
        $search = new \ONGR\ElasticsearchDSL\Search();

        $search->addQuery(
            new \ONGR\ElasticsearchDSL\Query\TermsQuery('categoryIds', [1, 2]), \ONGR\ElasticsearchDSL\Query\Compound\BoolQuery::FILTER
        );

        $search->addQuery(
            new \ONGR\ElasticsearchDSL\Query\TermsQuery('categoryIds', [1, 2]), \ONGR\ElasticsearchDSL\Query\Compound\BoolQuery::FILTER
        );
    }
}

?>
