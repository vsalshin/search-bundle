<?php

    namespace App\Application\Articul\SearchBundle\DependencyInjection\Compiler;


    use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
    use Symfony\Component\DependencyInjection\ContainerBuilder;
    use Symfony\Component\DependencyInjection\Reference;

    class ConfigSourcePass implements CompilerPassInterface
    {
        public function process(ContainerBuilder $container)
        {
            if (!$container->hasDefinition('articul_search.config_manager')) {
                return;
            }

            $sources = [];
            foreach (array_keys($container->findTaggedServiceIds('articul_search.config_source')) as $id) {
                $sources[] = new Reference($id);
            }

            $container->getDefinition('articul_search.config_manager')->replaceArgument(0, $sources);
        }

    }