<?php

namespace Itseasy\View;

use Laminas\View\Model\ViewModel as LaminasViewModel;
use Laminas\View\View as LaminasViewView;
use Laminas\View\ViewEvent as LaminasViewEvent;
use Psr\Http\Message\ResponseInterface as Response;

class View implements ViewInterface
{
    protected $view;
    protected $renderer;

    public function __construct()
    {
        $this->view = new LaminasViewView();
    }

    // Call registered ViewHelper in the renderer
    public function __call($function, $args)
    {
        $helper = $this->renderer->getHelperPluginManager();
        $function = $helper->get($function);

        return call_user_func_array($function, $args);
    }

    public function setRenderer($renderer)
    {
        $this->renderer = $renderer;
        $this->view->getEventManager()->attach(
            LaminasViewEvent::EVENT_RENDERER,
            static function () use ($renderer) {
                return $renderer;
            }
        );
    }

    public function setLayout(string $layout): void
    {
        $this->layout = $layout;
    }

    public function render(Response $response, string $template, array $variables = [], string $layout = ''): Response
    {
        $layoutvars = (empty($variables['layout']) ? [] : $variables['layout']);
        $variables = array_diff_key($variables, ['layout' => ['header' => []]]);

        $viewModel = new LaminasViewModel();
        $viewModel->setTemplate($template);
        $viewModel->setVariables($variables);

        $layout = $layout ?: $this->layout;
        $layoutModel = new LaminasViewModel();
        $layoutModel->setTemplate($layout);
        $layoutModel->setVariables($layoutvars);

        $layoutModel->setOption('has_parent', true);
        $layoutModel->addChild($viewModel);

        $response->getBody()->write($this->view->render($layoutModel));

        return $response;
    }

    public function renderJson(Response $response, array $variables = [], ?int $id = null): Response
    {
        $jsonModel = new LaminasViewModel();
        $jsonModel->setTemplate('layout/json');
        $jsonModel->setOption('has_parent', true);

        try {
            $variables = [
                'content' => json_encode([
                    'jsonrpc' => '2.0',
                    'id' => (is_null($id) ? time() : $id),
                    'result' => $variables,
                ]),
            ];
        } catch (Exception $e) {
            $variables = [
                'content' => json_encode([
                    'jsonrpc' => '2.0',
                    'id' => (is_null($id) ? time() : $id),
                    'error' => [
                        'code' => -32603,
                        'message' => $e->getMessage(),
                    ],
                ]),
            ];
        }

        $jsonModel->setVariables($variables);
        $response->getBody()->write($this->view->render($jsonModel));

        return $response->withHeader('Content-type', 'application/json');
    }
}
