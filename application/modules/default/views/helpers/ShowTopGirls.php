<?php

class Zend_View_Helper_ShowTopGirls
{
    public function ShowTopGirls()
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

        $view->salons = Application_Model_Kernel_Salon::getList('','salons.id BY DESC ', true, true, false, 1, false, false, 20, true, 'salons.recommend = 1');

        return $view->render('block/index/top_girls.phtml');
    }
}