<?php

namespace Itseasy\Navigation\Page;

use Laminas\Navigation\Page\AbstractPage;
use Psr\Http\Message\ServerRequestInterface;

class Uri extends AbstractPage
{
    protected $uri = null;
    protected $request;

    public function setUri($uri)
    {
        if (null !== $uri && ! is_string($uri)) {
            throw new Exception\InvalidArgumentException(
             'Invalid argument: $uri must be a string or null'
         );
        }

        $this->uri = $uri;
        return $this;
    }

    public function getUri()
    {
        return $this->uri;
    }

    public function getHref()
    {
        $uri = $this->getUri();

        $fragment = $this->getFragment();
        if (null !== $fragment) {
            if ('#' == substr($uri, -1)) {
                return $uri . $fragment;
            } else {
                return $uri . '#' . $fragment;
            }
        }

        return $uri;
    }

    public function isActive($recursive = false)
    {
        if (! $this->active) {
            if ($this->getRequest() instanceof ServerRequestInterface) {
                if ($this->getRequest()->getUri()->getPath() == $this->getUri()) {
                    $this->active = true;
                    return true;
                }
            }
        }

        return parent::isActive($recursive);
    }

    public function getRequest()
    {
        return $this->request;
    }

    public function setRequest(ServerRequestInterface $request = null)
    {
        $this->request = $request;
        return $this;
    }

    public function toArray()
    {
        return array_merge(
         parent::toArray(),
         [
             'uri' => $this->getUri(),
         ]
     );
    }
}
