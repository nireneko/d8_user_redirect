<?php

namespace Drupal\nireneko\EventSubscriber;

use Drupal\Core\Routing\CurrentRouteMatch;
use Drupal\Core\Session\AccountProxy;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Class NekoLoginCheckSubscriber.
 *
 * Ejemplo de como realizar una redirecion del usuario anonimo
 * https://nireneko.com/articulo/redirigir-usuario-anonimo-drupal
 */
class NekoLoginCheckSubscriber implements EventSubscriberInterface {


  /**
   * @var \Drupal\Core\Session\AccountProxy
   */
  private $account;

  /**
   * @var \Drupal\Core\Routing\CurrentRouteMatch
   */
  private $routeMatch;

  /**
   * Contructor de la clase NekoLoginCheckSubscriber.
   * Utilizado para realizar la inyeccion de dependencias.
   *
   * @param \Drupal\Core\Session\AccountProxy $accountProxy
   * @param \Drupal\Core\Routing\CurrentRouteMatch $currentRouteMatch
   */
  public function __construct(AccountProxy $accountProxy, CurrentRouteMatch $currentRouteMatch) {
    $this->account = $accountProxy;
    $this->routeMatch = $currentRouteMatch;
  }

  /**
   * Inidcamos cual es el evento al que nos suscribimos.
   */
  static function getSubscribedEvents() {
    $events[KernelEvents::REQUEST][] = ['checkAuthStatus', 10];
    return $events;
  }

  /**
   * @param \Symfony\Component\HttpKernel\Event\GetResponseEvent $event
   *
   * Metodo que responde al evento KernelEvents::Request
   */
  public function checkAuthStatus(GetResponseEvent $event) {

    //Comprobamos que el usuario sea anonimo
    if ($this->account->isAnonymous()) {

      //Obtenemos la ruta actual
      $currentRoute = $this->routeMatch->getRouteName();

      //Si la ruta es el login o registro no hace nada
      if ($currentRoute == 'user.login' || $currentRoute == 'user.register') {
        $event->stopPropagation();
        return;
      }

      //Si la ruta no es login o registro, redirige al login con un estado 201
      $response = new RedirectResponse('/user/login', 301);
      $event->setResponse($response);
      $event->stopPropagation();
    }
  }

}
