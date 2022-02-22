<?php


namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Bytes\ControllerPaginationBundle\Pagination\PaginationHelper;

/**
 * @param ContainerConfigurator $container
 */
return static function (ContainerConfigurator $container) {

    $services = $container->services();

    $services->set('bytes_controller_pagination.pagination', PaginationHelper::class)
        ->args([
            service('router.default'),
            0,  // $startOffset
            0,  // $endOffset
            0,  // $currentOffset
            [], // $parameterAllowlist
        ])
        ->alias(PaginationHelper::class, 'bytes_controller_pagination.pagination')
        ->public();
};
