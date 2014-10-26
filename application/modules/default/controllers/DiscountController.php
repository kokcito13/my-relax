<?php

class DiscountController extends Zend_Controller_Action
{

    public function preDispatch()
    {
        $this->view->blocksArray = array();
        $array = Application_Model_Kernel_Block::getList(true)->data;
        foreach ($array as $key => $value) {
            $this->view->blocksArray[$key] = $value->getContent()->getFields();
        }

        $this->view->menu = 'main';
    }

    public function showAction()
    {
        $this->view->idPage = (int)$this->_getParam('idPage');
        $this->view->discount = Application_Model_Kernel_Discount::getByIdPage($this->view->idPage);
        $this->view->contentPage = $this->view->discount->getContent()->getFields();

        $title = $this->view->contentPage['title']->getFieldText();
        $keywords = $this->view->contentPage['keywords']->getFieldText();
        $description = $this->view->contentPage['description']->getFieldText();

        $this->view->title = $title;
        $this->view->keywords = $keywords;
        $this->view->description = $description;
    }

    public function indexAction()
    {
        $city = Kernel_City::findCityFromUrl();
        $where = '';
        if ($city) {
            $this->view->contentPage = $city->getContent()->getFields();
            $where = 'salons.city_id = '.$city->getId();
        }

        $this->view->page = (int)$this->_getParam('page');
        $this->view->discounts = Application_Model_Kernel_Discount::getList(false, false, true, true, false, false, $this->view->page, 15, false, true, $where);

        if ($this->view->page == 1) {
            $this->view->headText = "<link rel='next' href='/skidki/page".($this->view->page+1).".html' />";
        } else {
            if ($this->view->page == 2)
                $this->view->headText = "<link rel='prev' href='/skidki/' />";
            else
                $this->view->headText = "<link rel='prev' href='/skidki/page".($this->view->page-1).".html' />";
            $this->view->headText .=  "<link rel='next' href='/skidki/page".($this->view->page+1).".html' />
                                    <link rel='canonical' href='/skidki/' />";
        }

        $this->view->title = 'Все виды эротического массажа от каталога салонов viprelax.';
        $this->view->keywords = 'виды, эротического, массажа';
        $this->view->description = 'Описания видов и элементов эротического массажа от салонов нашего каталога.';
    }
}