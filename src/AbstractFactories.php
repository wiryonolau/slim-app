<?php
declare(strict_types=1);

use DI\ContainerBuilder;
use Psr\Container\ContainerInterface;
use Zend\Db\Adapter\AdapterInterface;
use Zend\Db\ResultSet\HydratingResultSet;
use Zend\Db\TableGateway\TableGateway;
use Zend\Hydrator\ObjectPropertyHydrator;
use DI\Factory\RequestedEntry;

return function (ContainerBuilder $containerBuilder) {

    $containerBuilder->addDefinitions([

        'App\Domain\*\*Repository' => function (RequestedEntry $entry, ContainerInterface $c) {

            // get entity class name,
            // eg: "Post" by service named "App\Domain\Post\PostRepository"
            preg_match(
                '/(?<=App\\\\Domain\\\\)([A-Z][a-z]{1,})(?=\\\\\1Repository)/',
                $entry->getName(),
                $matches
            );
            $entity          = current($matches);
            $fullEntityClass = 'App\Domain' . str_repeat('\\' . $entity, 2);
            $fullRepoClass   = 'App\Infrastructure\Persistence' . '\\' . $entity . '\ZendDb' . $entity . 'Repository';

            $tableGateway = new TableGateway(
                $fullEntityClass::TABLE,
                $c->get(AdapterInterface::class),
                null,
                new HydratingResultSet(new ObjectPropertyHydrator(), new $fullEntityClass)
            );

            return new $fullRepoClass($tableGateway);
        },

    ]);
};
