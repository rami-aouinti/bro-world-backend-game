<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $container): void {
    if (!interface_exists(\Symfony\Component\Messenger\MessageBusInterface::class)) {
        return;
    }

    $container->extension('framework', [
        'messenger' => [
            'transports' => [
                'async_priority_high' => 'in-memory://',
                'async_priority_low' => 'in-memory://',
            ],
        ],
    ]);
};
