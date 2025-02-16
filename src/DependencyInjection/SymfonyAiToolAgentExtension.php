<?php

namespace ArnaudDelgerie\SymfonyAiToolAgent\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use ArnaudDelgerie\SymfonyAiToolAgent\Interface\ClientInterface;
use ArnaudDelgerie\SymfonyAiToolAgent\Interface\ToolFunctionManagerInterface;
use ArnaudDelgerie\SymfonyAiToolAgent\Interface\ConsoleToolFunctionManagerInterface;

class SymfonyAiToolAgentExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yaml');

        $container->registerForAutoconfiguration(ClientInterface::class)
            ->addTag('symfony_ai_tool_agent.ai_client');
        $container->registerForAutoconfiguration(ToolFunctionManagerInterface::class)
            ->addTag('symfony_ai_tool_agent.tool_function_manager');
        $container->registerForAutoconfiguration(ConsoleToolFunctionManagerInterface::class)
            ->addTag('symfony_ai_tool_agent.console_tool_function_manager');
    }

    public function getAlias(): string
    {
        return 'symfony_ai_tool_agent';
    }
}
