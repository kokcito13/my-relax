<?php

class GirlController extends Zend_Controller_Action
{

    public function preDispatch()
    {
        $this->view->blocksArray = array();
        $array = Application_Model_Kernel_Block::getList(true)->data;
        foreach ($array as $key => $value) {
            $this->view->blocksArray[$key] = $value->getContent()->getFields();
        }
    }

    public function showAction()
    {
        $this->view->idPage = (int)$this->_getParam('idPage');
        $this->view->girl = Application_Model_Kernel_Girl::getByIdPage($this->view->idPage);
        $this->view->contentPage = $this->view->girl->getContent()->getFields();

        $this->view->salon = $this->view->girl->getSalon();
        $this->view->salonContent = $this->view->salon->getContent()->getFields();

        $title = $this->view->contentPage['title']->getFieldText();
        $keywords = $this->view->contentPage['keywords']->getFieldText();
        $description = $this->view->contentPage['description']->getFieldText();


        $this->view->title = $title;
        $this->view->keywords = $keywords;
        $this->view->description = $description;

        $this->view->headText = isset($this->view->contentPage['head'])?$this->view->contentPage['head']->getFieldText():'';
    }
}