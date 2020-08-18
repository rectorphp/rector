<?php

declare(strict_types=1);

use Rector\Renaming\Rector\Name\RenameClassRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

# https://github.com/ongr-io/ElasticsearchDSL/blob/5.x/CHANGELOG.md
return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(RenameClassRector::class)
        ->call('configure', [[
            RenameClassRector::OLD_TO_NEW_CLASSES => [
                'ONGR\ElasticsearchDSL\Query\BoolQuery' => 'ONGR\ElasticsearchDSL\Query\Compound\BoolQuery',
                'ONGR\ElasticsearchDSL\Query\BoostingQuery' => 'ONGR\ElasticsearchDSL\Query\Compound\BoostingQuery',
                'ONGR\ElasticsearchDSL\Query\ConstantScoreQuery' => 'ONGR\ElasticsearchDSL\Query\Compound\ConstantScoreQuery',
                'ONGR\ElasticsearchDSL\Query\DisMaxQuery' => 'ONGR\ElasticsearchDSL\Query\Compound\DisMaxQuery',
                'ONGR\ElasticsearchDSL\Query\FunctionScoreQuery' => 'ONGR\ElasticsearchDSL\Query\Compound\FunctionScoreQuery',
                'ONGR\ElasticsearchDSL\Query\IndicesQuery' => 'ONGR\ElasticsearchDSL\Query\Compound\IndicesQuery',
                'ONGR\ElasticsearchDSL\Query\MatchQuery' => 'ONGR\ElasticsearchDSL\Query\FullText\MatchQuery',
                'ONGR\ElasticsearchDSL\Query\CommonTermsQuery' => 'ONGR\ElasticsearchDSL\Query\FullText\CommonTermsQuery',
                'ONGR\ElasticsearchDSL\Query\MatchPhrasePrefixQuery' => 'ONGR\ElasticsearchDSL\Query\FullText\MatchPhrasePrefixQuery',
                'ONGR\ElasticsearchDSL\Query\MatchPhraseQuery' => 'ONGR\ElasticsearchDSL\Query\FullText\MatchPhraseQuery',
                'ONGR\ElasticsearchDSL\Query\MultiMatchQuery' => 'ONGR\ElasticsearchDSL\Query\FullText\MultiMatchQuery',
                'ONGR\ElasticsearchDSL\Query\QueryStringQuery' => 'ONGR\ElasticsearchDSL\Query\FullText\QueryStringQuery',
                'ONGR\ElasticsearchDSL\Query\SimpleQueryStringQuery' => 'ONGR\ElasticsearchDSL\Query\FullText\SimpleQueryStringQuery',
                'ONGR\ElasticsearchDSL\Query\ExistsQuery' => 'ONGR\ElasticsearchDSL\Query\TermLevel\ExistsQuery',
                'ONGR\ElasticsearchDSL\Query\PrefixQuery' => 'ONGR\ElasticsearchDSL\Query\TermLevel\PrefixQuery',
                'ONGR\ElasticsearchDSL\Query\RangeQuery' => 'ONGR\ElasticsearchDSL\Query\TermLevel\RangeQuery',
                'ONGR\ElasticsearchDSL\Query\TermQuery' => 'ONGR\ElasticsearchDSL\Query\TermLevel\TermQuery',
                'ONGR\ElasticsearchDSL\Query\TermsQuery' => 'ONGR\ElasticsearchDSL\Query\TermLevel\TermsQuery',
                'ONGR\ElasticsearchDSL\Query\WildcardQuery' => 'ONGR\ElasticsearchDSL\Query\TermLevel\WildcardQuery',
                'ONGR\ElasticsearchDSL\Query\FuzzyQuery' => 'ONGR\ElasticsearchDSL\Query\TermLevel\FuzzyQuery',
                'ONGR\ElasticsearchDSL\Query\RegexpQuery' => 'ONGR\ElasticsearchDSL\Query\TermLevel\RegexpQuery',
                'ONGR\ElasticsearchDSL\Query\TypeQuery' => 'ONGR\ElasticsearchDSL\Query\TermLevel\TypeQuery',
                'ONGR\ElasticsearchDSL\Query\GeoBoundingBoxQuery' => 'ONGR\ElasticsearchDSL\Query\Geo\GeoBoundingBoxQuery',
                'ONGR\ElasticsearchDSL\Query\GeoDistanceQuery' => 'ONGR\ElasticsearchDSL\Query\Geo\GeoDistanceQuery',
                'ONGR\ElasticsearchDSL\Query\GeoDistanceRangeQuery' => 'ONGR\ElasticsearchDSL\Query\Geo\GeoDistanceRangeQuery',
                'ONGR\ElasticsearchDSL\Query\GeoPolygonQuery' => 'ONGR\ElasticsearchDSL\Query\Geo\GeoPolygonQuery',
                'ONGR\ElasticsearchDSL\Query\GeoShapeQuery' => 'ONGR\ElasticsearchDSL\Query\Geo\GeoShapeQuery',
                'ONGR\ElasticsearchDSL\Query\HasChildQuery' => 'ONGR\ElasticsearchDSL\Query\Joining\HasChildQuery',
                'ONGR\ElasticsearchDSL\Query\HasParentQuery' => 'ONGR\ElasticsearchDSL\Query\Joining\HasParentQuery',
                'ONGR\ElasticsearchDSL\Query\NestedQuery' => 'ONGR\ElasticsearchDSL\Query\Joining\NestedQuery',
                'ONGR\ElasticsearchDSL\Query\SpanContainingQuery' => 'ONGR\ElasticsearchDSL\Query\Span\SpanContainingQuery',
                'ONGR\ElasticsearchDSL\Query\SpanFirstQuery' => 'ONGR\ElasticsearchDSL\Query\Span\SpanFirstQuery',
                'ONGR\ElasticsearchDSL\Query\SpanMultiTermQuery' => 'ONGR\ElasticsearchDSL\Query\Span\SpanMultiTermQuery',
                'ONGR\ElasticsearchDSL\Query\SpanNearQuery' => 'ONGR\ElasticsearchDSL\Query\Span\SpanNearQuery',
                'ONGR\ElasticsearchDSL\Query\SpanNotQuery' => 'ONGR\ElasticsearchDSL\Query\Span\SpanNotQuery',
                'ONGR\ElasticsearchDSL\Query\SpanOrQuery' => 'ONGR\ElasticsearchDSL\Query\Span\SpanOrQuery',
                'ONGR\ElasticsearchDSL\Query\SpanWithinQuery' => 'ONGR\ElasticsearchDSL\Query\Span\SpanWithinQuery',
                'ONGR\ElasticsearchDSL\Query\MoreLikeThisQuery' => 'ONGR\ElasticsearchDSL\Query\Specialized\MoreLikeThisQuery',
                'ONGR\ElasticsearchDSL\Query\ScriptQuery' => 'ONGR\ElasticsearchDSL\Query\Specialized\ScriptQuery',
                'ONGR\ElasticsearchDSL\Query\TemplateQuery' => 'ONGR\ElasticsearchDSL\Query\Specialized\TemplateQuery',
            ],
        ]]);
};
