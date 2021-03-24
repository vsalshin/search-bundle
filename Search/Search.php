<?php

    namespace App\Application\Articul\SearchBundle\Search;


    use App\Application\Articul\SearchBundle\Entity\SearchIndex;
    use App\Application\Articul\SearchBundle\Type\Type;
    use App\Application\Articul\SearchBundle\Type\TypeManager;
    use App\Application\Sonata\MediaBundle\Entity\Media;
    use Doctrine\Common\Collections\Collection;
    use Doctrine\ORM\EntityManagerInterface;
    use Doctrine\ORM\Tools\Pagination\Paginator;
    use Symfony\Component\Debug\Exception\FatalErrorException;
    use Symfony\Component\Debug\Exception\UndefinedMethodException;
    use Symfony\Component\DependencyInjection\ContainerInterface;
    use Symfony\Component\Routing\RouterInterface;

    class Search
    {
        /**
         * @var TypeManager
         */
        private $typeManager;

        /**
         * @var EntityManagerInterface
         */
        private $entityManager;

        /**
         * @var \Doctrine\Bundle\DoctrineBundle\Registry|object
         */
        private $doctrine;

        /**
         * @var RouterInterface
         */
        private $router;

        /**
         * Search constructor.
         * @param TypeManager $typeManager
         * @param ContainerInterface $container
         */
        public function __construct(TypeManager $typeManager, ContainerInterface $container)
        {
            $this->typeManager = $typeManager;
            $this->doctrine = $container->get('doctrine');
            $this->router = $container->get('router');
        }


        /**
         * Clear index table and create new index from all types
         * @return $this
         * @throws UndefinedMethodException
         * @throws \Doctrine\DBAL\DBALException
         */
        public function createIndex() {
            $this->truncateSearch();
            $types = $this->typeManager->getTypes();

            foreach ($types as $type) {
                $items = $this->entityManager->getRepository($type->getModel())->findBy($type->getFilters());
                foreach ($items as $item) {
                    $indexItem = $this->fillSearchItem($item, $type);
                    $this->addSearchItem($indexItem);
                }
                $this->commit();
            }
            return $this;
        }

        /**
         * Add index item to entity manager
         * @param SearchIndex $item
         * @return $this
         */
        private function addSearchItem(SearchIndex $item) {
            if($item->getUrl() != null && !empty($item->getUrl())) {
                $this->getEntityManager()->persist($item);
            }
            return $this;
        }

        /**
         * @param $object
         * @param Type $type
         * @throws UndefinedMethodException
         */
        public function addItemToIndex($object, Type $type) {
            if(!$this->checkFilter($object, $type)) {
                return;
            }
            $searchItem = $this->fillSearchItem($object, $type);
            $this->addSearchItem($searchItem)->commit();
        }

        /**
         * Update index item in entity manager
         * @param SearchIndex $item
         * @return $this
         */
        private function updateSearchItem(SearchIndex $item) {
            $this->getEntityManager()->merge($item);
            return $this;
        }

        /**
         * @param $object
         * @param Type $type
         * @throws UndefinedMethodException
         */
        public function updateItemToIndex($object, Type $type) {
            $entityId = $this->getFieldValue($object, $type->getObjectId());
            $searchItem = $this->getSearchIndex([
                'entityType' => $type->getModel(),
                'entityId' => $entityId,
                'entityCode' => $type->getName(),
            ]);

            $allowUpdate = $this->checkFilter($object, $type);

            if(!$searchItem && $allowUpdate) {
                $searchItem = $this->fillSearchItem($object, $type);
                $this->addSearchItem($searchItem)->commit();
                return;
            }

            if($searchItem && !$allowUpdate) {
                $this->deleteSearchIndex($searchItem)->commit();
                return;
            }

            if($searchItem && $allowUpdate) {
                $searchItem = $this->fillSearchItem($object, $type, $searchItem);
                $this->updateSearchItem($searchItem)->commit();
                return;
            }
        }

        /**
         * @param $object
         * @param Type $type
         * @return bool
         * @throws UndefinedMethodException
         */
        private function checkFilter($object, Type $type) {
            foreach ($type->getFilters() as $key => $value) {
                if($this->getFieldValue($object, $key) != $value) {
                    return false;
                }
            }
            return true;
        }

        /**
         * @param array $criteria
         * @return SearchIndex|null|object
         */
        public function getSearchIndex(array $criteria) {
            $indexRepo = $this->getEntityManager()->getRepository(SearchIndex::class);
            return $indexRepo->findOneBy($criteria);
        }

        /**
         * Delete search index item from table
         * @param SearchIndex $index
         * @return $this
         */
        private function deleteSearchIndex(SearchIndex $index) {
            $this->getEntityManager()->remove($index);
            return $this;
        }

        /**
         * @param $object
         * @param Type $type
         * @throws UndefinedMethodException
         */
        public function deleteItemFromIndex($object, Type $type) {
            $entityId = $this->getFieldValue($object, $type->getObjectId());
            $searchItem = $this->getSearchIndex([
                'entityType' => $type->getModel(),
                'entityId' => $entityId,
                'entityCode' => $type->getName(),
            ]);
            if($searchItem) {
                $this->deleteSearchIndex($searchItem);
            }
        }

        /**
         * Commit changes of entity manager
         * @return $this
         */
        private function commit() {
            $this->getEntityManager()->flush();
            return $this;
        }


        /**
         * Create Search Index item
         * @param $object
         * @param Type $type
         * @param SearchIndex|null $indexItem
         * @return SearchIndex
         * @throws UndefinedMethodException
         */
        public function fillSearchItem($object, Type $type, SearchIndex $indexItem = null) {
            if($indexItem == null) {
                $indexItem = new SearchIndex();
            }

            $indexItem->setEntityType($type->getModel());
            $searchableContent = [
                'title' => null,
                'body' => null,
                'properties' => null,
            ];

            $entityId = $this->getFieldValue($object, $type->getObjectId());
            $indexItem->setEntityId($entityId);

            if($type->getEntityName()) {
                $indexItem->setEntityName($type->getEntityName());
            }
            $indexItem->setEntityCode($type->getName());

            if($type->getTitle()) {
                $title = $this->getFieldValue($object, $type->getTitle());
                $indexItem->setTitle($title);
                $searchableContent[] = $title;
            }

            if($type->getBody()) {
                $body = $this->getFieldValue($object, $type->getBody());
                $indexItem->setBody($body);
                $searchableContent[] = $body;
            }
            if($type->getProperties()) {
                foreach ($type->getProperties() as $property) {
                    $searchableContent[] = $this->getFieldValue($object, $property);
                }
            }
            $searchableContent = implode("\n\n", array_filter($searchableContent));
            $indexItem->setSearchableContent($searchableContent);

            if($route = $type->getRoute()) {
                if($route['entity_property']) {
                    $url = $this->getFieldValue($object, $route['entity_property']);
                    if(!empty($url)) {
                        $indexItem->setUrl($this->router->generate($url));
                    }
                }
                else {
                    $parameters = [];
                    foreach ($route['parameters'] as $key => $parameter) {
                        $parameters[$key] = $this->getFieldValue($object, $parameter);
                    }
                    //$this->router->generate('app_individuals_index_rentofsales'
                    $indexItem->setUrl($this->router->generate($route['name'], $parameters));
                }
            }
            return $indexItem;
        }


        /**
         * Get property value $field of object $item
         * @param $item
         * @param string $field
         * @return string
         * @throws UndefinedMethodException
         */
        private function getFieldValue($item, string $field) {

            $method = false;
            foreach (['get', 'is'] as $prefix) {
                $tmp = sprintf('%s%s', $prefix, ucfirst($field));
                if(method_exists($item, $tmp)) {
                    $method = $tmp;
                    break;
                }
            }
            if($method === false) {
                throw new UndefinedMethodException(sprintf("Methods [%s, %s] doesn't exist for field %s! Model %s. Please create it!",
                    sprintf('%s%s', 'get', ucfirst($field)), sprintf('%s%s', 'is', ucfirst($field)), $field, get_class($item)),
                    new FatalErrorException(sprintf("Call to undefined method %s", $method), 400, 10, __FILE__,
                        __LINE__)
                );
            }

            $value = call_user_func([$item, $method]);
            if(is_array($value)) {
                $c = implode("\n\n", array_map([$this, 'stripTags'], $value));
            }
            else {
                $c = $this->stripTags($value);
            }
            return $c;
        }

        /**
         * Remove all tags from field value
         * @param $value
         * @return string
         * @throws \Exception
         */
        private function stripTags($value) {
            if($value instanceof Collection) {
                $value = $value->toArray();
                $searchable = [];
                foreach ($value as $item) {
                    if(is_object($item)) {
                        if(method_exists($item, 'getName')) {
                            $searchable[] = strip_tags(call_user_func([$item, 'getName']));
                        }
                        else if(method_exists($item, 'getTitle')){
                            $searchable[] = strip_tags(call_user_func([$item, 'getTitle']));
                        }
                        else if(method_exists($item, 'getFile')) {
                            $searchable[] = $this->stripTags(call_user_func([$item, 'getFile']));
                        }
                    }
                    else {
                        $searchable[] = strip_tags($item);
                    }
                }
                return implode("\n\n", $searchable);
            }
            if($value instanceof \DateTime) {
                return $value->format("d.m.Y");
            }
            if($value instanceof Media) {
                return $value->getName();
            }
            try {
                return strip_tags($value);
            }
            catch (\Exception $exception) {
                echo get_class($value);
                throw $exception;
            }
        }

        /**
         * Make search Query
         * @param $query
         * @param int $limit
         * @param int $offset
         * @param bool $entity
         * @return array
         */
        public function search($query, $limit = 20, $offset = 1, $entity = false) {
            $qb = $this->getEntityManager()->createQueryBuilder();
            $qb1 = $this->getEntityManager()->createQueryBuilder();

            $queryGroup = $qb->addSelect('count(s.id) as count')
                ->addSelect('s.entityType')
                ->addSelect('s.entityName')
                ->addSelect('s.entityCode')
                ->from(SearchIndex::class, 's')
                ->where('s.searchableContent LIKE :search')
                ->setParameter('search', '%' . $query . '%')
                ->groupBy('s.entityType');

            $query = $qb1->select('s1')
                ->from(SearchIndex::class, 's1')
                ->where('s1.searchableContent LIKE :search')
                ->setParameter('search', '%' . $query . '%')
                ->setMaxResults($limit)
                ->setFirstResult($limit * ($offset - 1));
            if($entity) {
                $query->andWhere('s1.entityCode = :entity')
                    ->setParameter('entity', $entity);
            }
            $paginator = new Paginator($query, $fetchJoinCollection = true);

            return [
                'paginator' => $paginator,
                'groups' => $queryGroup->getQuery()->execute()
            ];
        }

        /**
         * Clear Search Index table
         * @throws \Doctrine\DBAL\DBALException
         */
        private function truncateSearch() {
            $connection = $this->getEntityManager()->getConnection();
            $platform = $connection->getDatabasePlatform();
            $connection->executeQuery('SET FOREIGN_KEY_CHECKS = 0;');
            $connection->executeUpdate($platform->getTruncateTableSQL('search_index'));
            $connection->executeQuery('SET FOREIGN_KEY_CHECKS = 1;');
        }

        /**
         * @return TypeManager
         */
        public function getTypeManager(): TypeManager
        {
            return $this->typeManager;
        }

        /**
         * @param TypeManager $typeManager
         */
        public function setTypeManager(TypeManager $typeManager): void
        {
            $this->typeManager = $typeManager;
        }

        /**
         * @return EntityManagerInterface
         */
        public function getEntityManager(): EntityManagerInterface
        {
            if($this->entityManager == null) {
                $this->entityManager = $this->doctrine->getManager();
            }
            return $this->entityManager;
        }

        /**
         * @param EntityManagerInterface $entityManager
         */
        public function setEntityManager(EntityManagerInterface $entityManager): void
        {
            $this->entityManager = $entityManager;
        }

        /**
         * @return RouterInterface
         */
        public function getRouter(): RouterInterface
        {
            return $this->router;
        }

        /**
         * @param RouterInterface $router
         */
        public function setRouter(RouterInterface $router): void
        {
            $this->router = $router;
        }
    }