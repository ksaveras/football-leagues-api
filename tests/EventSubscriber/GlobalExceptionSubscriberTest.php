<?php

namespace App\Tests\EventSubscriber;

use App\EventSubscriber\GlobalExceptionSubscriber;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * Class GlobalExceptionSubscriberTest.
 */
class GlobalExceptionSubscriberTest extends TestCase
{
    public function testGetSubscribedEvents(): void
    {
        $events = GlobalExceptionSubscriber::getSubscribedEvents();

        $this->assertArrayHasKey('kernel.exception', $events);
    }

    /**
     * @dataProvider exceptionEventProvider
     *
     * @param GetResponseForExceptionEvent $event
     * @param string                       $content
     * @param int                          $statusCode
     */
    public function testOnKernelException(GetResponseForExceptionEvent $event, string $content, int $statusCode): void
    {
        $subscriber = new GlobalExceptionSubscriber();
        $subscriber->onKernelException($event);

        $response = $event->getResponse();

        $this->assertEquals($content, $response->getContent());
        $this->assertEquals($statusCode, $response->getStatusCode());
    }

    /**
     * @return \Generator
     */
    public function exceptionEventProvider(): ?\Generator
    {
        $kernel = $this->createMock(HttpKernelInterface::class);
        $request = $this->createMock(Request::class);
        $exception = new \Exception('General error');

        $event = new GetResponseForExceptionEvent(
            $kernel, $request, HttpKernelInterface::MASTER_REQUEST, $exception
        );

        yield [$event, '{"error":"General error"}', 500];

        $kernel = $this->createMock(HttpKernelInterface::class);
        $request = $this->createMock(Request::class);
        $exception = new NotFoundHttpException('Entity not fount');

        $event = new GetResponseForExceptionEvent(
            $kernel, $request, HttpKernelInterface::MASTER_REQUEST, $exception
        );

        yield [$event, '{"error":"Entity not fount"}', 404];

        $kernel = $this->createMock(HttpKernelInterface::class);
        $request = $this->createMock(Request::class);
        $exception = new NotFoundHttpException('Entity not fount');
        $responseMock = new JsonResponse(['error' => 'Bad data'], 400);

        $event = new GetResponseForExceptionEvent(
            $kernel, $request, HttpKernelInterface::MASTER_REQUEST, $exception
        );
        $event->setResponse($responseMock);

        yield [$event, '{"error":"Bad data"}', 400];
    }
}
