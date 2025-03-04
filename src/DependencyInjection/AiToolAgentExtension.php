<?php

namespace ArnaudDelgerie\AiToolAgent\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use ArnaudDelgerie\AiToolAgent\Interface\ClientInterface;
use ArnaudDelgerie\AiToolAgent\Interface\ToolFunctionManagerInterface;
use ArnaudDelgerie\AiToolAgent\Interface\ConsoleToolFunctionManagerInterface;

class AiToolAgentExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yaml');

        $container->registerForAutoconfiguration(ClientInterface::class)
            ->addTag('ai_tool_agent.ai_client');
        $container->registerForAutoconfiguration(ToolFunctionManagerInterface::class)
            ->addTag('ai_tool_agent.tool_function_manager');
        $container->registerForAutoconfiguration(ConsoleToolFunctionManagerInterface::class)
            ->addTag('ai_tool_agent.console_tool_function_manager');
    }

    public function getAlias(): string
    {
        return 'ai_tool_agent';
    }
}
