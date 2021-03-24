<?php

    namespace App\Application\Articul\SearchBundle\Type;


    class TypeManager
    {
        /**
         * @var Type[]|array
         */
        private $types;

        /**
         * TypeManager constructor.
         * @param Type[] $types
         */
        public function __construct(array $types)
        {
            $this->types = $types;
        }

        /**
         * @return Type[]|array
         */
        public function getTypes()
        {
            return $this->types;
        }

        /**
         * Return list of mapped entities linked to type
         * @return array
         */
        public function getMappedEntities() {
            $entities = [];
            array_map(function (Type $type) use(&$entities) {
                $entities[$type->getModel()] = $type->getName();
            }, $this->types);
            return $entities;
        }

        /**
         * @param Type[]|array $types
         */
        public function setTypes($types): void
        {
            $this->types = $types;
        }

        public function getType(string $name) {
            return $this->types[$name] ?: null;
        }
    }