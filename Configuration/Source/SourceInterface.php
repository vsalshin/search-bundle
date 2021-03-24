<?php

    namespace App\Application\Articul\SearchBundle\Configuration\Source;


    use App\Application\Articul\SearchBundle\Configuration\TypeConfig;

    interface SourceInterface
    {
        /**
         * @return TypeConfig[]
         */
        public function getConfiguration();
    }