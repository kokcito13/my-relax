<?php

class Zend_View_Helper_ShowWidgetComments
{
    public function ShowWidgetComments()
    {
        $view = new Zend_View(array('basePath' => APPLICATION_PATH . '/modules/default/views'));
        $view->city = Kernel_City::findCityFromUrl();
        $view->cityContent = false;
        if ($view->city) {
            $view->cityContent = $view->city->getContent()->getFields();
        }

        $view->blocks = Application_Model_Kernel_Block::getList(true)->data;
        foreach ($view->blocks as $key => $value) {
            $view->blocks[$key] = $value->getContent()->getFields();
        }

        $view->comments = Application_Model_Kernel_Comment::getList(false, 1, 3);

        return $view->render('block/sidebar/widget_comments.phtml');
    }
}