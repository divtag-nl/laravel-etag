<?php

use Divtag\LaravelEtag\Etag;
use Divtag\LaravelEtag\NotModifiedException;
use Illuminate\Support\Facades\Request;
use PHPUnit\Framework\TestCase;

class EtagTest extends TestCase
{
    public function testUpdates()
    {
        $etag = new Etag();

        $etag->update('asd');

        $this->assertEquals('W/"eV56Zr5eoP/U7I7Jcz0eqA"', $etag->get());
        $this->assertEquals('W/"eV56Zr5eoP/U7I7Jcz0eqA"', $etag->__toString());

        $etag->update('foo', 'bar');

        $this->assertEquals('W/"IxOA6WjYzl6NEGujTjAjVg"', $etag->get());
        $this->assertEquals('W/"IxOA6WjYzl6NEGujTjAjVg"', $etag->__toString());
    }

    public function testMatch()
    {
        $this->assertTrue(Etag::match('W/"eV56Zr5eoP/U7I7Jcz0eqA"', '*'));
        $this->assertTrue(Etag::match('W/"eV56Zr5eoP/U7I7Jcz0eqA"', 'W/"eV56Zr5eoP/U7I7Jcz0eqA"'));
        $this->assertNotTrue(Etag::match('W/"eV56Zr5eoP/U7I7Jcz0eqA"', 'W/"IxOA6WjYzl6NEGujTjAjVg"'));
    }

    public function testAbortIfMatch()
    {
        Request::shouldReceive('hasHeader')
            ->once()
            ->with('If-None-Match')
            ->andReturn(true);

        Request::shouldReceive('header')
            ->once()
            ->with('If-None-Match')
            ->andReturn('W/"eV56Zr5eoP/U7I7Jcz0eqA"');

        $this->expectException(NotModifiedException::class);

        (new Etag())->update('asd')->abortIfMatch();
    }
}
