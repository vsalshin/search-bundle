<?php

    namespace App\Application\Articul\SearchBundle\EventListener;


    use App\Application\Articul\SearchBundle\Entity\SearchIndex;
    use App\Application\Articul\SearchBundle\Search\Search;
    use App\Application\Articul\SearchBundle\Type\Type;
    use App\Application\Articul\SearchBundle\Type\TypeManager;
    use Doctrine\Common\EventSubscriber;
    use Doctrine\ORM\Event\LifecycleEventArgs;
    use Symfony\Component\Debug\Exception\UndefinedMethodException;
    use Symfony\Component\DependencyInjection\ContainerInterface;

    class SearchIndexerSubscriber implements EventSubscriber
    {
        /**
         * @var TypeManager
         */
        private $typeManager;

        /**
         * @var Search
         */
        private $search;

        public function getSubscribedEvents()
        {
            return [
                'postUpdate',
                'postPersist',
                'preRemove',
            ];
        }

        /**
         * SearchIndexerSubscriber constructor.
         * @param ContainerInterface $container
         */
        public function __construct(ContainerInterface $container)
        {
            $this->search = $container->get('articul_search.search');
            $this->typeManager = $this->search->getTypeManager();
        }

        /**
         * @param LifecycleEventArgs $args
         * @throws UndefinedMethodException
         */
        public function postPersist(LifecycleEventArgs $args) {
            $entity = $args->getEntity();
            $type = $this->getType(get_class($entity));

            if(!$type) {
                return;
            }

            $this->search->addItemToIndex($entity, $type);
        }

        /**
         * Post update handler - update search index item in index table
         * @param LifecycleEventArgs $args
         * @throws UndefinedMethodException
         */
        public function postUpdate(LifecycleEventArgs $args) {
            $entity = $args->getEntity();
            $type = $this->getType(get_class($entity));

            if(!$type) {
                return;
            }

            $this->search->updateItemToIndex($entity, $type);
        }

        /**
         * Post Delete Handler - remove index item from index table
         * @param LifecycleEventArgs $args
         * @throws UndefinedMethodException
         */
        public function preRemove(LifecycleEventArgs $args) {

            $entity = $args->getEntity();
            if($entity instanceof SearchIndex) {
                return;
            }
            $type = $this->getType(get_class($entity));

            if(!$type) {
                return;
            }
            $this->search->deleteItemFromIndex($entity, $type);
        }

        /**
         * @param string $entity - class name
         * @return Type|null - Linked type of entity
         */
        private function getType(string $entity) {
            $mappedEntities = $this->typeManager->getMappedEntities();
            return isset($mappedEntities[$entity]) ? $this->typeManager->getType($mappedEntities[$entity]) : null;
        }
    }