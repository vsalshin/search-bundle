<?php

    namespace App\Application\Articul\SearchBundle\Type;


    class Type
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
            $props = $this->getConfig('properties');
            if($props) {
                return array_keys($props);
            }
            return null;
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

        public function getObjectId() {
            return 'id';
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
        public function getRoute() {
            return $this->getConfig('route');
        }

        /**
         * @return array|null|string
         */
        public function getEntityName() {
            return $this->getConfig('entityName');
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
            return isset($this->config[$key]) ? $this->config[$key] : null;
        }
    }