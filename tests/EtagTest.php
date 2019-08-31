<?php

use Divtag\LaravelEtag\Etag;
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
}
