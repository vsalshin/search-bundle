<?php

    namespace App\Application\Articul\SearchBundle\Configuration\Source;


    use App\Application\Articul\SearchBundle\Configuration\TypeConfig;

    class ContainerSource implements SourceInterface
    {
        /**
         * The internal container representation of information.
         *
         * @var array $config
         */
        private $config;

        /**
         * @return TypeConfig[]|array
         */
        public function getConfiguration()
        {
            $types = [];
            foreach ($this->config as $config) {
                $type = new TypeConfig($config['name'], $config);

                $types[$config['name']] = $type;
            }
            return $types;
        }
    }