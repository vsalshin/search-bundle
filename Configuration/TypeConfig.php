<?php

    namespace App\Application\Articul\SearchBundle\Configuration;


    class TypeConfig
    {
        /**
         * @var array
         */
        private $config;

        /**
         * @var string
         */
        private $name;

        /**
         * @param string $name
         * @param array  $config
         */
        public function __construct($name, array $config = [])
        {
            $this->config = $config;
            $this->name = $name;
        }

        /**
         * @return string|null
         */
        public function getModel()
        {
            return $this->getConfig('model');
        }

        /**
         * @return null|array
         */
        public function getProperties() {
            return $this->getConfig('properties');
        }

        /**
         * @return array|null|string
         */
        public function getBody() {
            return $this->getConfig('body');
        }

        /**
         * @return array|null|string
         */
        public function getTitle() {
            return $this->getConfig('title');
        }

        /**
         * @return string
         */
        public function getName()
        {
            return $this->name;
        }

        /**
         * @return array|null|string
         */
        public function getFilters() {
            return $this->getConfig('filters');
        }

        /**
         * @param string $key
         *
         * @return null|string|array
         */
        private function getConfig($key)
        {
            return $this->config[$key] ?: null;
        }
    }