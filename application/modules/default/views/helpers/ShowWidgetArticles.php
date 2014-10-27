<?php

class Zend_View_Helper_ShowWidgetArticles
{
    public function ShowWidgetArticles()
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

        $view->articles = Application_Model_Kernel_Article::getList(' article.id ', ' DESC ', true, true, false, false, false, false, 3, true, false);

        return $view->render('block/sidebar/widget_articles.phtml');
    }
}