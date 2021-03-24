<?php

    namespace App\Application\Articul\SearchBundle\DependencyInjection\Compiler;


    use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
    use Symfony\Component\DependencyInjection\ContainerBuilder;
    use Symfony\Component\DependencyInjection\Reference;

    class TypePass implements CompilerPassInterface
    {

        /**
         * @param ContainerBuilder $container
         */
        public function process(ContainerBuilder $container)
        {
            if (!$container->hasDefinition('articul_search.type_manager')) {
                return;
            }

            $types = [];
            foreach ($container->findTaggedServiceIds('articul_search.type') as $id => $tags) {
                foreach ($tags as $tag) {
                    $types[$tag['name']] = new Reference($id);
                }
            }

            $container->getDefinition('articul_search.type_manager')->replaceArgument(0, $types);
        }

    }