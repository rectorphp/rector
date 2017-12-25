<?php declare(strict_types=1);


/**
 * @author Tobias Schultze <http://tobion.de>
 */
class SomeClassWithFalsyAnnotations
{
    /**
     * Get the session store used by the guard.
     *
     * @param \Illuminate\Contracts\Session\Session. $session
     * @return \Illuminate\Contracts\Session\Session.
     */
    public function getSession($session)
    {
    }
}
