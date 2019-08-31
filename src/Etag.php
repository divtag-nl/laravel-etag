<?php

namespace Divtag\LaravelEtag;

class Etag
{
    /**
     * @var string
     */
    private $etag = '';

    /**
     * @param string ...$tags
     * @return self
     */
    public function update(string ...$tags): self
    {
        foreach ($tags as $tag) {
            $this->etag = hash_hmac('md5', $tag, $this->etag, true);
        }

        return $this;
    }

    /**
     * @return string
     */
    public function get(): string
    {
        return 'W/"' . rtrim(base64_encode($this->etag), '=') . '"';
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->get();
    }

    /**
     * @param string $etag
     * @param string $match
     * @return bool
     */
    public static function match(string $etag, string $match): bool
    {
        if ($match === '*') {
            return true;
        }

        // Get request etags
        preg_match_all('/(W\/)?".+?"/', $match, $matches);

        return in_array($etag, $matches[0]);
    }
}
