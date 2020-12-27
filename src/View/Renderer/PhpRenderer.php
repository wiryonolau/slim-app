<?php
declare(strict_types=1);

namespace Itseasy\View\Renderer;

use Slim\Views\PhpRenderer as SlimPhpRenderer;
use Itseasy\View\Helper\ViewHelperInterface;
use ReflectionClass;

class PhpRenderer extends SlimPhpRenderer
{
    protected $templateSuffix = "phtml";
    protected $viewHelper = [];

    public function __call($function, $args) {
        if (isset($this->viewHelper[$function])) {
            return call_user_func_array($this->viewHelper[$function], $args);
        }
    }

    public function setTemplateSuffix(string $suffix) : void {
        $this->templateSuffix = $suffix;
    }

    public function getTemplateSuffix() : string {
        return $this->templateSuffix;
    }

    public function addViewHelper($helper, $name = "") {
        if (is_null($name) or $name == "") {
            $class = new ReflectionClass($helper);
            $name = lcfirst($class->getShortName());
        }

        $this->viewHelper[$name] = $helper;
    }

    public function fetch(string $template, array $data = [], bool $useLayout = false): string
    {
        $layoutdata = (empty($data["layout"]) ? [] : $data["layout"]);
        $data = array_diff_key($data, ["layout" => []]);

        $output = $this->fetchTemplate($template, $data);
        if ($this->layout !== null && $useLayout) {
            $layoutdata['content'] = $output;
            $output = $this->fetchTemplate($this->layout, $layoutdata);
        }

        return $output;
    }

    public function setLayout(string $layout): void
    {
        if ($layout !== '' or $layout != null) {
            $layout = sprintf("%s.%s", $layout, $this->templateSuffix);
            parent::setLayout($layout);
        }
    }

    public function fetchTemplate(string $template, array $data = []): string
    {
        $template = sprintf("%s.%s", $template, $this->templateSuffix);
        return parent::fetchTemplate($template, $data);
    }

    protected function protectedIncludeScope(string $template, array $data): void
    {
        $helper = $this->viewHelper;
        extract($data);
        include func_get_arg(0);
    }
}
