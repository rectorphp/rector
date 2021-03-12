<?php

namespace Rector\CodeQuality\Tests\Rector\Ternary\UnnecessaryTernaryExpressionRector\Fixture;

function unnecessaryTernaryExpression()
{
    $direction = $directions[$attribute] === SORT_DESC ? SORT_ASC : SORT_DESC;
}
