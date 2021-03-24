<?php

    namespace App\Application\Articul\SearchBundle\Command;

    use App\Application\Articul\SearchBundle\Entity\SearchIndex;
    use App\Application\Articul\SearchBundle\Type\TypeManager;
    use Doctrine\ORM\EntityManagerInterface;
    use Psr\Container\ContainerInterface;
    use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
    use Symfony\Component\Console\Command\Command;
    use Symfony\Component\Console\Input\InputInterface;
    use Symfony\Component\Console\Output\OutputInterface;
    use Symfony\Component\Debug\Exception\UndefinedMethodException;
    use Symfony\Component\Routing\RouterInterface;


    class CreateCommand extends ContainerAwareCommand
    {

        protected static $defaultName = 'articul:search:create';

        protected function configure()
        {
            $this
                ->setName('articul:search:create')
                ->setDescription('Creating index with mapping')
            ;
        }

        protected function execute(InputInterface $input, OutputInterface $output)
        {
            $this->getContainer()->get('articul_search.search')->createIndex();
        }
    }