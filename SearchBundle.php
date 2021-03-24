<?php

    namespace App\Application\Articul\SearchBundle;


    use App\Application\Articul\SearchBundle\DependencyInjection\ArticulSearchExtension;
    use App\Application\Articul\SearchBundle\DependencyInjection\Compiler\ConfigSourcePass;
    use App\Application\Articul\SearchBundle\DependencyInjection\Compiler\TypePass;
    use Symfony\Component\DependencyInjection\ContainerBuilder;
    use Symfony\Component\HttpKernel\Bundle\Bundle;

    class SearchBundle extends Bundle
    {
        public function build(ContainerBuilder $container)
        {
            parent::build($container);
            $container->addCompilerPass(new ConfigSourcePass());
            $container->addCompilerPass(new TypePass());
        }

        public function getContainerExtension()
        {
            return new ArticulSearchExtension();
        }
    }