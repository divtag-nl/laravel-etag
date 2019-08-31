<?php

use Divtag\LaravelEtag\EtagMiddleware;
use Illuminate\Http\Request;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;

class EtagMiddlewareTest extends TestCase
{
    public function testResponseWithoutEtag()
    {
        $request = new Request();

        $response = (new EtagMiddleware())->handle($request, function () {
            return new Response('Foobar');
        });

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('Foobar', $response->getContent());
        $this->assertEquals('W/"idVzm6q7vmW+NcvmHIjgbQ"', $response->getEtag());
    }

    public function testResponseWithEtag()
    {
        $request = new Request();

        $response = (new EtagMiddleware())->handle($request, function () {
            return new Response('Foobar', 200, ['Etag' => '"Foobar"']);
        });

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('Foobar', $response->getContent());
        $this->assertEquals('"Foobar"', $response->getEtag());
    }

    public function testRequestWithAndResponseWithoutEtag()
    {
        $request = new Request();

        $request->headers->set('If-None-Match', 'W/"idVzm6q7vmW+NcvmHIjgbQ"');

        $response = (new EtagMiddleware())->handle($request, function () {
            return new Response('Foobar');
        });

        $this->assertEquals(304, $response->getStatusCode());
        $this->assertEquals('', $response->getContent());
        $this->assertEquals('W/"idVzm6q7vmW+NcvmHIjgbQ"', $response->getEtag());
    }

    public function testRequestWithWrongEtag()
    {
        $request = new Request();

        $request->headers->set('If-None-Match', '"foobar"');

        $response = (new EtagMiddleware())->handle($request, function () {
            return new Response('Foobar');
        });

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('Foobar', $response->getContent());
        $this->assertEquals('W/"idVzm6q7vmW+NcvmHIjgbQ"', $response->getEtag());
    }

    public function testHeadRequestWithAndResponseWithoutEtag()
    {
        $request = new Request();

        $request->setMethod('HEAD');
        $request->headers->set('If-None-Match', 'W/"idVzm6q7vmW+NcvmHIjgbQ"');

        $response = (new EtagMiddleware())->handle($request, function () {
            return new Response('Foobar');
        });

        $this->assertEquals(304, $response->getStatusCode());
        $this->assertEquals('', $response->getContent());
        $this->assertEquals('W/"idVzm6q7vmW+NcvmHIjgbQ"', $response->getEtag());
    }

    public function testRequestWithAndResponseWithEtag()
    {
        $request = new Request();

        $request->headers->set('If-None-Match', 'W/"Foobar"');

        $response = (new EtagMiddleware())->handle($request, function () {
            return new Response('Foobar', 200, ['Etag' => 'W/"Foobar"']);
        });

        $this->assertEquals(304, $response->getStatusCode());
        $this->assertEquals('', $response->getContent());
        $this->assertEquals('W/"Foobar"', $response->getEtag());
    }

    public function testRequestWithStrongAndResponseWithEtag()
    {
        $request = new Request();

        $request->headers->set('If-None-Match', '"Foobar"');

        $response = (new EtagMiddleware())->handle($request, function () {
            return new Response('Foobar', 200, ['Etag' => '"Foobar"']);
        });

        $this->assertEquals(304, $response->getStatusCode());
        $this->assertEquals('', $response->getContent());
        $this->assertEquals('"Foobar"', $response->getEtag());
    }

    public function testRequestWithStrongInSteadOfWeakAndResponseWithEtag()
    {
        $request = new Request();

        $request->headers->set('If-None-Match', '"idVzm6q7vmW+NcvmHIjgbQ"');

        $response = (new EtagMiddleware())->handle($request, function () {
            return new Response('Foobar');
        });

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('Foobar', $response->getContent());
        $this->assertEquals('W/"idVzm6q7vmW+NcvmHIjgbQ"', $response->getEtag());
    }

    public function testRequestMultiple1()
    {
        $request = new Request();

        $request->headers->set('If-None-Match', '"Foo", "Bar"');

        $response = (new EtagMiddleware())->handle($request, function () {
            return new Response('Foobar');
        });

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('Foobar', $response->getContent());
        $this->assertEquals('W/"idVzm6q7vmW+NcvmHIjgbQ"', $response->getEtag());
    }

    public function testRequestMultiple2()
    {
        $request = new Request();

        $request->headers->set('If-None-Match', '"Foo", "Bar"');

        $response = (new EtagMiddleware())->handle($request, function () {
            return new Response('Foobar', 200, ['Etag' => '"Foo"']);
        });

        $this->assertEquals(304, $response->getStatusCode());
        $this->assertEquals('', $response->getContent());
        $this->assertEquals('"Foo"', $response->getEtag());
    }

    public function testRequestMultiple3()
    {
        $request = new Request();

        $request->headers->set('If-None-Match', 'W/"Foo", W/"idVzm6q7vmW+NcvmHIjgbQ"');

        $response = (new EtagMiddleware())->handle($request, function () {
            return new Response('Foobar');
        });

        $this->assertEquals(304, $response->getStatusCode());
        $this->assertEquals('', $response->getContent());
        $this->assertEquals('W/"idVzm6q7vmW+NcvmHIjgbQ"', $response->getEtag());
    }

    public function testIfNoneMatchWildcard()
    {
        $request = new Request();

        $request->headers->set('If-None-Match', '*');

        $response = (new EtagMiddleware())->handle($request, function () {
            return new Response('Foobar');
        });

        $this->assertEquals(304, $response->getStatusCode());
        $this->assertEquals('', $response->getContent());
        $this->assertEquals('W/"idVzm6q7vmW+NcvmHIjgbQ"', $response->getEtag());
    }

    public function testIf304isNotEtagable()
    {
        $request = new Request();

        $response = (new EtagMiddleware())->handle($request, function () {
            return (new Response())->setNotModified();
        });

        $this->assertEquals(304, $response->getStatusCode());
        $this->assertEquals(null, $response->headers->get('Etag'));
    }
}
