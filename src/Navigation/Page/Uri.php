<?php
declare(strict_types = 1);

namespace Itseasy\Navigation\Page;

use Laminas\Navigation\Page\AbstractPage;
use Psr\Http\Message\ServerRequestInterface;

class Uri extends AbstractPage
{
    protected $uri = null;
    protected $request;

    public function setUri($uri) : self
    {
        if (null !== $uri && ! is_string($uri)) {
            throw new Exception\InvalidArgumentException(
                'Invalid argument: $uri must be a string or null'
         );
        }

        $this->uri = $uri;
        return $this;
    }

    public function getUri() : ?string
    {
        return $this->uri;
    }

    public function getHref() : ?string
    {
        $uri = $this->getUri();

        if (!empty($this->getCustomProperties()["query"])) {
            $uri = $uri .'?'. http_build_query($this->getCustomProperties()["query"]);
        }

        $fragment = $this->getFragment();
        if (null !== $fragment) {
            if ('#' == substr($uri, -1)) {
                $uri = $uri . $fragment;
            } else {
                $uri = $uri . '#' . $fragment;
            }
        }

        return $uri;
    }

    public function isActive($recursive = false) : bool
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

    public function getRequest() : ServerRequestInterface
    {
        return $this->request;
    }

    public function setRequest(ServerRequestInterface $request = null) : self
    {
        $this->request = $request;
        return $this;
    }

    public function toArray() : array
    {
        return array_merge(
            parent::toArray(),
            [
             'uri' => $this->getUri(),
            ]
        );
    }
}
