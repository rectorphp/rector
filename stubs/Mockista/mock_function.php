<?php

if (function_exists('mock')) {
    return;
}

function mock(): \Mockery\MockInterface
{
    return new DummyMock();
}
