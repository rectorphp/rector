<?php

namespace Rector\Symfony5\Tests\Rector\Class_\LogoutHandlerToLogoutEventSubscriberRector\Fixture;

use Symfony\Component\Security\Http\Logout\LogoutHandlerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

final class SomeLogoutHandler implements LogoutHandlerInterface
{
    public function logout(Request $request, Response $response, TokenInterface $token)
    {
        $result = $request;
        $nextResult = $token;
    }
}

?>
-----
<?php

namespace Rector\Symfony5\Tests\Rector\Class_\LogoutHandlerToLogoutEventSubscriberRector\Fixture;

use Symfony\Component\Security\Http\Logout\LogoutHandlerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

final class SomeLogoutHandler implements \Symfony\Component\EventDispatcher\EventSubscriberInterface
{
    public function onLogout(\Symfony\Component\Security\Http\Event\LogoutEvent $logoutEvent): void
    {
        $request = $logoutEvent->getRequest();
        $token = $logoutEvent->getToken();
        $result = $request;
        $nextResult = $token;
    }
    /**
     * @return array<string, mixed>
     */
    public static function getSubscribedEvents(): array
    {
        return [\Symfony\Component\Security\Http\Event\LogoutEvent::class => 'onLogout'];
    }
}

?>
