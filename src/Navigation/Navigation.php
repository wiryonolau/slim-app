<?php
declare(strict_types = 1);

namespace Itseasy\Navigation;

use Laminas\Navigation\Navigation as LaminasNavigation;
use Laminas\Navigation\AbstractContainer;
use Itseasy\Navigation\Page\Uri;

class Navigation
{
    protected $config = [];
    protected $attributes = [];
    protected $containers = [];

    public function __construct(array $config = [])
    {
        $this->config = $config;
    }

    public function setAttribute(string $key, $value) : void
    {
        $this->attributes[$key] = $value;
    }

    public function getContainer(?string $name = "default", $rebuild = false) : AbstractContainer
    {
        if (is_null($name)) {
            $name = "default";
        }

        if (empty($this->containers[$name]) or $rebuild) {
            $this->containers[$name] = new LaminasNavigation($this->getPages($name));
        }
        return $this->containers[$name];
    }

    protected function getPages(string $name = "default") : array
    {
        $pages = $this->config[$name];
        return $this->injectAttributes($pages);
    }

    protected function injectAttributes($pages) : array
    {
        foreach ($pages as &$page) {
            $page["type"] = Uri::class;

            foreach ($this->attributes as $key => $value) {
                $page[$key] = $value;
            }

            if (isset($page["pages"])) {
                $page["pages"] = $this->injectAttributes($page["pages"]);
            }
        }
        return $pages;
    }
}
