<?php

namespace AbcB
{
    const VALUE = 'value';
}

namespace LegacyB
{
    use AbcB;

    const VALUE = AbcB\VALUE;
}
