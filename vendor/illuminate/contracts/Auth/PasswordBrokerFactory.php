<?php

namespace RectorPrefix202507\Illuminate\Contracts\Auth;

interface PasswordBrokerFactory
{
    /**
     * Get a password broker instance by name.
     *
     * @param  string|null  $name
     * @return \Illuminate\Contracts\Auth\PasswordBroker
     */
    public function broker($name = null);
}
