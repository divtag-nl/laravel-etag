<?php

namespace Divtag\LaravelEtag;

use Closure;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class EtagMiddleware
{
    /**
     * @param Request $request
     * @param Closure $next
     * @return Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        return $this->isEtagable($request, $response)
            ? $this->handleWithEtag($request, $response)
            : $response;
    }

    /**
     * @param Request  $request
     * @param Response $response
     * @return bool
     */
    protected function isEtagable(Request $request, Response $response): bool
    {
        return in_array($request->getMethod(), ['GET', 'HEAD'])
            && $response->getStatusCode() !== 304;
    }

    /**
     * @param Request  $request
     * @param Response $response
     * @return Response
     */
    protected function handleWithEtag(Request $request, Response $response): Response
    {
        if ($response->headers->has('Etag')) {
            $etag = $response->headers->get('Etag');
        } else {
            $response->headers->set('ETag', $etag = $this->generateEtag($response));
        }

        $ifNoneMatch = $request->headers->get('If-None-Match');

        return $ifNoneMatch !== null && Etag::match($etag, $ifNoneMatch)
            ? $response->setNotModified()
            : $response;
    }

    /**
     * @param Response $response
     * @return string
     */
    protected function generateEtag(Response $response): string
    {
        return (new Etag())->update($response->getContent())->get();
    }
}
