<?php

    namespace App\Application\Articul\SearchBundle\Configuration;


    use App\Application\Articul\SearchBundle\Configuration\Source\SourceInterface;

    class ConfigManager
    {
        /**
         * @var TypeConfig[]
         */
        private $types = [];

        /**
         * @param SourceInterface[] $sources
         */
        public function __construct(array $sources)
        {
            $this->types = $sources;
        }

        /**
         * {@inheritdoc}
         */
        public function getTypeConfiguration($typeName)
        {
            $type = $this->types[$typeName];

            if (!$type) {
                throw new \InvalidArgumentException(sprintf('Type with name "%s" is not configured', $typeName));
            }

            return $type;
        }

        /**
         * @param $typeName
         * @return bool
         */
        public function hasTypeConfiguration($typeName)
        {
            return isset($this->types[$typeName]);
        }
    }