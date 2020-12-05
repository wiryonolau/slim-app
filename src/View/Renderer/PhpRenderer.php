<?php
declare(strict_types=1);

namespace App\View\Renderer;

use Slim\Views\PhpRenderer as SlimPhpRenderer;
use App\View\Helper\ViewHelperInterface;
use ReflectionClass;

class PhpRenderer extends SlimPhpRenderer
{
    protected $viewHelper = [];

    public function __call($function, $args) {
        if (isset($this->viewHelper[$function])) {
            return call_user_func_array($this->viewHelper[$function], $args);
        }
    }

    public function addViewHelper($helper, $name = "") {
        if (is_null($name) or $name == "") {
            $class = new ReflectionClass($helper);
            $name = lcfirst($class->getShortName());
        }

        $this->viewHelper[$name] = $helper;
    }

    protected function protectedIncludeScope(string $template, array $data): void
    {
        $helper = $this->viewHelper;
        extract($data);
        include func_get_arg(0);
    }
}
