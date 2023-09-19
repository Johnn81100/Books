<?php

namespace App\EventSubscriber;

use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ExceotionSubscriber implements EventSubscriberInterface
{
    /**
    *  Gère les exceptions du noyau et renvoie une réponse JSON.
    *
    * @param ExceptionEvent $event L'événement de l'exception.
    * @throws \Symfony\Component\HttpKernel\Exception\HttpException Si l'exception est une instance de HttpException.
    * @return void
    */
    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        if ($exception instanceof HttpException) {
            $data = [
                'status' => $exception->getStatusCode(),
                'message' => $exception->getMessage()
            ];

            $event->setResponse(new JsonResponse($data));
      } else {
            $data = [
                'status' => 500, // Le status n'existe pas car ce n'est pas une exception HTTP, donc on met 500 par défaut.
                'message' => $exception->getMessage()
            ];

            $event->setResponse(new JsonResponse($data));
      }
    }
    /**
    * Récupère la liste des événements auxquels la fonction PHP est abonnée.
    *
    * @return array La liste des événements auxquels la fonction est abonnée.
    */
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => 'onKernelException',
        ];
    }
}
