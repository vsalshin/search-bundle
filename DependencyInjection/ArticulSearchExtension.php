<?php

    namespace App\Application\Articul\SearchBundle\DependencyInjection;


    use Symfony\Component\Config\FileLocator;
    use Symfony\Component\DependencyInjection\ChildDefinition;
    use Symfony\Component\DependencyInjection\ContainerBuilder;
    use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
    use Symfony\Component\DependencyInjection\Reference;
    use Symfony\Component\HttpKernel\DependencyInjection\Extension;


    class ArticulSearchExtension extends Extension
    {
        /**
         * An array of indexes as configured by the extension.
         *
         * @var array
         */
        private $typesConfigs = [];

        /**
         * If we've encountered a type mapped to a specific persistence driver, it will be loaded
         * here.
         *
         * @var array
         */
        private $loadedDrivers = [];

        public function load(array $configs, ContainerBuilder $container)
        {
            $configuration = $this->getConfiguration($configs, $container);
            $config = $this->processConfiguration($configuration, $configs);

            $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));

            if (empty($config['mapping']) || empty($config['mapping']['types'])) {
                // No indexed data are defined
                return;
            }

            foreach (['config', 'source', 'type', 'commands', 'doctrine'] as $basename) {
                $loader->load(sprintf('%s.xml', $basename));
            }

            $this->loadTypes($config['mapping']['types'], $container);

            //$container->setAlias('articul_search.type', sprintf('articul_search.type.%s', $config['default_index']));
            //$container->getAlias('articul_search.type')->setPublic(true);
            //$container->setParameter('articul_search.default_index', $config['default_index']);

            $container->getDefinition('articul_search.config_source.container')->replaceArgument(0, $this->typesConfigs);

            $this->loadTypeManager($container);

            //$this->createDefaultManagerAlias($config['default_manager'], $container);
        }

        /**
         * @param array            $config
         * @param ContainerBuilder $container
         *
         * @return Configuration
         */
        public function getConfiguration(array $config, ContainerBuilder $container)
        {
            return new Configuration();
        }

        /**
         * Load Types
         * @param array $types
         * @param ContainerBuilder $container
         */
        private function loadTypes(array $types, ContainerBuilder $container)
        {

            foreach ($types as $name => $type) {
                $typeId = sprintf('articul_search.type.%s', $name);

                $indexDef = new ChildDefinition('articul_search.type_prototype');
                //$indexDef->setFactory([new Reference('fos_elastica.client'), 'getIndex']);
                $indexDef->replaceArgument(0, $name);
                $indexDef->replaceArgument(1, $type);
                $indexDef->addTag('articul_search.type', [
                    'name' => $name,
                ]);


                $container->setDefinition($typeId, $indexDef);

                $reference = new Reference($typeId);

                $this->typesConfigs[$name] = [
                    'reference' => $reference,
                    'name' => $name,
                ];
            }
        }

        /*
         * @param array $types
         * @param ContainerBuilder $container
         */
       /* private function loadTypes(array $types, ContainerBuilder $container)
        {
            foreach ($types as $name => $type) {

                $typeId = sprintf('fos_elastica.type.%s', $name);
                //$typeName = sprintf('fos_elastica.type.%s', $name);

                $typeDef = new ChildDefinition('fos_elastica.type_prototype');
                $typeDef->setFactory([$indexConfig['reference'], 'getType']);
                $typeDef->replaceArgument(0, $name);

                $container->setDefinition($typeId, $typeDef);

                $typeConfig = [
                    'name' => $name,
                    'mapping' => [], // An array containing anything that gets sent directly to ElasticSearch
                    'config' => [],
                ];

                foreach ([
                             'dynamic_templates',
                             'properties',
                             '_all',
                             '_id',
                             '_parent',
                             '_routing',
                             '_source',
                         ] as $field) {
                    if (isset($type[$field])) {
                        $typeConfig['mapping'][$field] = $type[$field];
                    }
                }

                foreach ([
                             'persistence',
                             'serializer',
                             'analyzer',
                             'search_analyzer',
                             'dynamic',
                             'date_detection',
                             'dynamic_date_formats',
                             'numeric_detection',
                         ] as $field) {
                    $typeConfig['config'][$field] = array_key_exists($field, $type) ?
                        $type[$field] :
                        null;
                }

                $this->indexConfigs[$indexName]['types'][$name] = $typeConfig;

                if (isset($type['persistence'])) {
                    $this->loadTypePersistenceIntegration($type['persistence'], $container, new Reference($typeId), $indexName, $name);

                    $typeConfig['persistence'] = $type['persistence'];
                }

                if (isset($type['_parent'])) {
                    // _parent mapping cannot contain `property` and `identifier`, so removing them after building `persistence`
                    unset($indexConfig['types'][$name]['mapping']['_parent']['property'], $indexConfig['types'][$name]['mapping']['_parent']['identifier']);
                }

                if (isset($type['indexable_callback'])) {
                    $indexableCallbacks[sprintf('%s/%s', $indexName, $name)] = $this->buildCallback($type['indexable_callback'], $name);
                }

                if ($container->hasDefinition('fos_elastica.serializer_callback_prototype')) {
                    $typeSerializerId = sprintf('%s.serializer.callback', $typeId);
                    $typeSerializerDef = new ChildDefinition('fos_elastica.serializer_callback_prototype');

                    if (isset($type['serializer']['groups'])) {
                        $typeSerializerDef->addMethodCall('setGroups', [$type['serializer']['groups']]);
                    }

                    if (isset($type['serializer']['serialize_null'])) {
                        $typeSerializerDef->addMethodCall('setSerializeNull', [$type['serializer']['serialize_null']]);
                    }

                    if (isset($type['serializer']['version'])) {
                        $typeSerializerDef->addMethodCall('setVersion', [$type['serializer']['version']]);
                    }

                    $typeDef->addMethodCall('setSerializer', [[new Reference($typeSerializerId), 'serialize']]);
                    $container->setDefinition($typeSerializerId, $typeSerializerDef);
                }
            }
        }*/

        /*
         * Map Elastica to Doctrine events for the current driver.
         */
        /*private function getDoctrineEvents(array $typeConfig)
        {
            switch ($typeConfig['driver']) {
                case 'orm':
                    $eventsClass = '\Doctrine\ORM\Events';
                    break;
                case 'phpcr':
                    $eventsClass = '\Doctrine\ODM\PHPCR\Event';
                    break;
                case 'mongodb':
                    $eventsClass = '\Doctrine\ODM\MongoDB\Events';
                    break;
                default:
                    throw new \InvalidArgumentException(sprintf('Cannot determine events for driver "%s"', $typeConfig['driver']));
            }

            $events = [];
            $eventMapping = [
                'insert' => [constant($eventsClass.'::postPersist')],
                'update' => [constant($eventsClass.'::postUpdate')],
                'delete' => [constant($eventsClass.'::preRemove')],
                'flush' => [constant($eventsClass.'::postFlush')],
            ];

            foreach ($eventMapping as $event => $doctrineEvents) {
                if (isset($typeConfig['listener'][$event]) && $typeConfig['listener'][$event]) {
                    $events = array_merge($events, $doctrineEvents);
                }
            }

            return $events;
        }*/

        /**
         * Loads the index manager.
         *
         * @param ContainerBuilder $container
         **/
        private function loadTypeManager(ContainerBuilder $container)
        {
            $typeRefs = array_map(function ($type) {
                return $type['reference'];
            }, $this->typesConfigs);

            $managerDef = $container->getDefinition('articul_search.type_manager');
            $managerDef->replaceArgument(0, $typeRefs);
        }

        /**
         * Makes sure a specific driver has been loaded.
         *
         * @param ContainerBuilder $container
         * @param string           $driver
         */
        private function loadDriver(ContainerBuilder $container, $driver)
        {
            if (in_array($driver, $this->loadedDrivers)) {
                return;
            }

            $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
            $loader->load($driver.'.xml');
            $this->loadedDrivers[] = $driver;
        }


        /**
         * Creates a default manager alias for defined default manager or the first loaded driver.
         *
         * @param string           $defaultManager
         * @param ContainerBuilder $container
         */
        /*private function createDefaultManagerAlias($defaultManager, ContainerBuilder $container)
        {
            if (0 == count($this->loadedDrivers)) {
                return;
            }

            if (count($this->loadedDrivers) > 1
                && in_array($defaultManager, $this->loadedDrivers)
            ) {
                $defaultManagerService = $defaultManager;
            } else {
                $defaultManagerService = $this->loadedDrivers[0];
            }

            $container->setAlias('fos_elastica.manager', sprintf('fos_elastica.manager.%s', $defaultManagerService));
            $container->getAlias('fos_elastica.manager')->setPublic(true);
            $container->setAlias(RepositoryManagerInterface::class, 'fos_elastica.manager');
            $container->getAlias(RepositoryManagerInterface::class)->setPublic(false);
        }*/
    }