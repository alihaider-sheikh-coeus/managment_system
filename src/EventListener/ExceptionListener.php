<?php
namespace App\EventListener;


use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
class ExceptionListener
{
    public function onKernelException(ExceptionEvent $event)
    {

        $exception = $event->getThrowable();
        $message=$exception->getMessage();
        $response= new JsonResponse(["message"=>$message], Response::HTTP_UNPROCESSABLE_ENTITY);
        $event->setResponse($response);
    }


}