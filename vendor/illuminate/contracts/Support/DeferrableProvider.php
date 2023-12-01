<?php

namespace RectorPrefix202312\Illuminate\Contracts\Support;

interface DeferrableProvider
{
    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides();
}
