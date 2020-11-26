<?php
declare(strict_types=1);

namespace App\View\Renderer;

use Slim\Views\PhpRenderer as SlimPhpRenderer;
use App\View\Helper\ViewHelperInterface;

class PhpRenderer extends SlimPhpRenderer
{
    protected $viewHelper = [];

    public function __call($function, $args) {
        if (isset($this->viewHelper[$function])) {
            return call_user_func_array($this->viewHelper[$function], $args);
        }
    }

    public function addViewHelper(ViewHelperInterface $helper) {
        if ($helper->getName()) {
            $this->viewHelper[$helper->getName()] = $helper;
        }
    }

    protected function protectedIncludeScope(string $template, array $data): void
    {
        $helper = $this->viewHelper;
        extract($data);
        include func_get_arg(0);
    }
}
