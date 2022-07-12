<?php

declare(strict_types=1);

namespace Itseasy\View\Renderer\Factory;

use Laminas\Stdlib\ArrayUtils;
use Laminas\View\HelperPluginManager;
use Laminas\View\Renderer\PhpRenderer;
use Laminas\View\Resolver\TemplatePathStack;
use Psr\Container\ContainerInterface;

class PhpRendererFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $config = $container->get('Config')->getConfig();
        $viewConfig = $config['view'];
        $viewHelperConfig = $config['view_helpers'];

        // alias for first letter capital to overwrite laminas default helper
        foreach ($viewHelperConfig['aliases'] as $alias => $helper) {
            $viewHelperConfig['aliases'][ucfirst($alias)] = $helper;
        }

        $templateResolver = new TemplatePathStack([
            'script_paths' => [$viewConfig['template_path']],
            'default_suffix' => $viewConfig['default_template_suffix'],
        ]);

        // Create the renderer
        $renderer = new PhpRenderer();
        $renderer->setResolver($templateResolver);

        $helper_config = [];

        // Merge laminas helper with our own helper
        $helper_config = ArrayUtils::merge($helper_config, $viewHelperConfig);
        $pluginManager = new HelperPluginManager($container, $helper_config);

        $renderer->setHelperPluginManager($pluginManager);

        return $renderer;
    }
}
