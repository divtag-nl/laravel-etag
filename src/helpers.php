<?php

use Divtag\LaravelEtag\Etag;
use Divtag\LaravelEtag\Facade;

function entity_tag()
{
    /** @var Etag $etag */
    $etag = Facade::getFacadeRoot();

    if (func_num_args()) {
        $etag->update(...func_get_args());
    }

    return $etag;
}
