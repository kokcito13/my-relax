<?php

class Zend_View_Helper_ShowSalonHeader {

    public function ShowSalonHeader($salon){
        $view = new Zend_View(array('basePath'=>APPLICATION_PATH.'/modules/default/views'));
        
        $view->salon = $salon;
        $view->contentPage = $salon->getContent()->getFields();

        $comments = $salon->getComments();

        $view->countAllComments = count($comments);

        $view->blocks = Application_Model_Kernel_Block::getList(true)->data;
        foreach ($view->blocks as $key => $value) {
            $view->blocks[$key] = $value->getContent()->getFields();
        }

        return $view->render('block/salon_header.phtml');
    }
}