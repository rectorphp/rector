<?php

namespace Rector\Symfony5\Tests\Rector\Class_\LogoutHandlerToLogoutEventSubscriberRector\Fixture;

use Symfony\Component\Security\Http\Logout\LogoutHandlerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

final class LogoutHandlerWithoutAnyUsage implements LogoutHandlerInterface
{
    /**
     * @var int
     */
    private $value;

    public function logout(Request $request, Response $response, TokenInterface $token)
    {
        $this->value = 1000;
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

final class LogoutHandlerWithoutAnyUsage implements \Symfony\Component\EventDispatcher\EventSubscriberInterface
{
    /**
     * @var int
     */
    private $value;
    public function onLogout(\Symfony\Component\Security\Http\Event\LogoutEvent $logoutEvent): void
    {
        $this->value = 1000;
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
