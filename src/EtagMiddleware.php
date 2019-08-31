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

        return $ifNoneMatch !== null && $this->doesMatch($etag, $ifNoneMatch)
            ? $response->setNotModified()
            : $response;
    }

    /**
     * @param string $etag
     * @param string $match
     * @return bool
     */
    protected function doesMatch(string $etag, string $match): bool
    {
        if ($match === '*') {
            return true;
        }

        // Get request etags
        preg_match_all('/(W\/)?".+?"/', $match, $matches);

        return in_array($etag, $matches[0]);
    }

    /**
     * @param Response $response
     * @return string
     */
    protected function generateEtag(Response $response): string
    {
        $hash = hash('md5', $response->getContent(), true);

        return 'W/"' . rtrim(base64_encode($hash), '=') . '"';
    }
}
