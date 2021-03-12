<?php

namespace Rector\Tests\CodeQuality\Rector\Ternary\UnnecessaryTernaryExpressionRector\Fixture;

function unnecessaryTernaryExpression()
{
    $direction = $directions[$attribute] === SORT_DESC ? SORT_ASC : SORT_DESC;
}
