<?php


namespace Bytes\ControllerPaginationBundle\DependencyInjection;


use Exception;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;

class BytesControllerPaginationExtension extends Extension implements ExtensionInterface
{
    /**
     * @param array $configs
     * @param ContainerBuilder $container
     * @throws Exception
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new PhpFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.php');

        $configuration = $this->getConfiguration($configs, $container);

        /** @var array $config = ['offsets' => ['start' => 1, 'end' => 1, 'current' => 1], 'parameters_allowlist' => []] */
        $config = $this->processConfiguration($configuration, $configs);

        $allowlist = [];
        if (array_key_exists('parameters_allowlist', $config)) {
            $allowlist = $config['parameters_allowlist'];
        }

        $definition = $container->getDefinition('bytes_controller_pagination.pagination');
        $definition->replaceArgument(1, $config['offsets']['start']);
        $definition->replaceArgument(2, $config['offsets']['end']);
        $definition->replaceArgument(3, $config['offsets']['current']);
        $definition->replaceArgument(4, $allowlist);
    }
}
