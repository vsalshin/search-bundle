<?php

    namespace App\Application\Articul\SearchBundle\Entity;


    use Doctrine\ORM\Mapping as ORM;

    /**
     * Class SearchIndex
     * @ORM\Entity()
     * @package App\Application\Articul\SearchBundle\Entity
     */
    class SearchIndex
    {
        /**
         * @var integer|null $id
         * @ORM\Id
         * @ORM\GeneratedValue
         * @ORM\Column(type="integer")
         */
        private $id;

        /**
         * @var string|null $entityType
         * @ORM\Column(type="string")
         */
        private $entityType;

        /**
         * @var string|null $entityName
         * @ORM\Column(type="string", nullable=true)
         */
        private $entityName;

        /**
         * @var string|null $entityCode
         * @ORM\Column(type="string")
         */
        private $entityCode;

        /**
         * @var string|null $url
         * @ORM\Column(type="string", nullable=true)
         */
        private $url;

        /**
         * @var string|null $title
         * @ORM\Column(type="string", nullable=true)
         */
        private $title;

        /**
         * @var string|null $body
         * @ORM\Column(type="text", nullable=true)
         */
        private $body;

        /**
         * @var string|null $searchableContent
         * @ORM\Column(type="text", nullable=true)
         */
        private $searchableContent;

        /**
         * @var integer|null $entityId
         * @ORM\Column(type="integer")
         */
        private $entityId;

        /**
         * @return int|null
         */
        public function getId(): ?int
        {
            return $this->id;
        }

        /**
         * @param int|null $id
         */
        public function setId(?int $id): void
        {
            $this->id = $id;
        }

        /**
         * @return null|string
         */
        public function getEntityType(): ?string
        {
            return $this->entityType;
        }

        /**
         * @param null|string $entityType
         */
        public function setEntityType(?string $entityType): void
        {
            $this->entityType = $entityType;
        }

        /**
         * @return null|string
         */
        public function getEntityName(): ?string
        {
            return $this->entityName;
        }

        /**
         * @param null|string $entityName
         */
        public function setEntityName(?string $entityName): void
        {
            $this->entityName = $entityName;
        }

        /**
         * @return null|string
         */
        public function getEntityCode(): ?string
        {
            return $this->entityCode;
        }

        /**
         * @param null|string $entityCode
         */
        public function setEntityCode(?string $entityCode): void
        {
            $this->entityCode = $entityCode;
        }

        /**
         * @return null|string
         */
        public function getUrl(): ?string
        {
            return $this->url;
        }

        /**
         * @param null|string $url
         */
        public function setUrl(?string $url): void
        {
            $this->url = $url;
        }

        /**
         * @return null|string
         */
        public function getTitle(): ?string
        {
            return $this->title;
        }

        /**
         * @param null|string $title
         */
        public function setTitle(?string $title): void
        {
            $this->title = $title;
        }

        /**
         * @return null|string
         */
        public function getBody(): ?string
        {
            return $this->body;
        }

        /**
         * @param null|string $body
         */
        public function setBody(?string $body): void
        {
            $this->body = $body;
        }

        /**
         * @return null|string
         */
        public function getSearchableContent(): ?string
        {
            return $this->searchableContent;
        }

        /**
         * @param null|string $searchableContent
         */
        public function setSearchableContent(?string $searchableContent): void
        {
            $this->searchableContent = $searchableContent;
        }

        /**
         * @return int|null
         */
        public function getEntityId(): ?int
        {
            return $this->entityId;
        }

        /**
         * @param int|null $entityId
         */
        public function setEntityId(?int $entityId): void
        {
            $this->entityId = $entityId;
        }
    }