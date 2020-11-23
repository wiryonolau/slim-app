<?php
declare(strict_types=1);

namespace App\View\Renderer;

use Slim\Views\PhpRenderer as SlimPhpRenderer;
use App\View\Helper\ViewHelperInterface;

class PhpRenderer extends SlimPhpRenderer
{
    protected $viewHelper;

    public function addViewHelper(ViewHelperInterface $helper) {
        if ($helper->getName()) {
            $this->viewHelper[$helper->getName()] = $helper;
        }
    }

    public function fetchTemplate(string $template, array $data = []): string
    {
        $helper = $this->viewHelper;
        return parent::fetchTemplate($template, $data);
    }
}
