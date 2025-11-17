<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $container): void {
    if (!interface_exists(\Symfony\Component\Messenger\MessageBusInterface::class)) {
        return;
    }

    $container->extension('framework', [
        'messenger' => [
            'buses' => [
                'command_bus' => [
                    'middleware' => [
                        'doctrine_ping_connection',
                        'doctrine_close_connection',
                    ],
                ],
            ],
            'failure_transport' => 'failed',
            'transports' => [
                'async_priority_high' => [
                    'dsn' => '%env(MESSENGER_TRANSPORT_DSN)%',
                    'options' => [
                        'exchange' => [
                            'name' => 'job_high',
                        ],
                        'queues' => [
                            'messages_job_high' => null,
                        ],
                    ],
                    'retry_strategy' => [
                        'max_retries' => 3,
                        'delay' => 5000,
                        'multiplier' => 2,
                        'max_delay' => 0,
                    ],
                ],
                'async_priority_low' => [
                    'dsn' => '%env(MESSENGER_TRANSPORT_DSN)%',
                    'options' => [
                        'exchange' => [
                            'name' => 'low',
                        ],
                        'queues' => [
                            'messages_low' => null,
                        ],
                    ],
                    'retry_strategy' => [
                        'max_retries' => 3,
                        'delay' => 5000,
                        'multiplier' => 2,
                        'max_delay' => 0,
                    ],
                ],
                'failed' => [
                    'dsn' => 'doctrine://default?queue_name=failed',
                    'retry_strategy' => [
                        'service' => 'Bro\\WorldCoreBundle\\Infrastructure\\Messenger\\Strategy\\FailedRetry',
                    ],
                ],
            ],
            'routing' => [
                'Bro\\WorldCoreBundle\\Domain\\Message\\Interfaces\\MessageHighInterface' => 'async_priority_high',
                'Bro\\WorldCoreBundle\\Domain\\Message\\Interfaces\\MessageLowInterface' => 'async_priority_low',
            ],
        ],
    ]);
};
