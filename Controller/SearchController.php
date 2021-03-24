<?php

    namespace App\Application\Articul\SearchBundle\Controller;


    use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
    use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

    /**
     * Class SearchController
     * @Route("search/")
     * @package App\Application\Articul\SearchBundle\Controller
     */
    class SearchController extends AbstractController
    {
        /**
         * @Route("")
         * @return \Symfony\Component\HttpFoundation\Response
         */
        public function index() {
            return $this->render('@Search/search-page.html.twig');
        }
    }