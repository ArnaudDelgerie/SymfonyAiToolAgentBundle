<?php

namespace ArnaudDelgerie\SymfonyAiToolAgent\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use ArnaudDelgerie\SymfonyAiToolAgent\Interface\AiClientInterface;
use ArnaudDelgerie\SymfonyAiToolAgent\Interface\ToolFunctionManagerInterface;

class SymfonyAiToolAgentExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yaml');

        $container->registerForAutoconfiguration(AiClientInterface::class)
            ->addTag('symfony_ai_tool_agent.ai_client');
        $container->registerForAutoconfiguration(ToolFunctionManagerInterface::class)
            ->addTag('symfony_ai_tool_agent.tool_function_manager');
    }

    public function getAlias(): string
    {
        return 'symfony_ai_tool_agent';
    }
}
