<?php

class Zend_View_Helper_ShowTopGirlCity
{
    public function ShowTopGirlCity()
    {
        $view = new Zend_View(array('basePath' => APPLICATION_PATH . '/modules/default/views'));
        $view->city = Kernel_City::findCityFromUrl();
        $view->cityContent = false;
        if ($view->city) {
            $view->cityContent = $view->city->getContent()->getFields();
        }

        $where = 'salons.city_id = '.$view->city->getId();
        $view->salons = Application_Model_Kernel_Salon::getList('salons.call_price', "DESC", true, true, false, 1, 1, Application_Model_Kernel_Salon::ITEM_ON_PAGE, false, true, $where);

        $view->blocks = Application_Model_Kernel_Block::getList(true)->data;
        foreach ($view->blocks as $key => $value) {
            $view->blocks[$key] = $value->getContent()->getFields();
        }

        return $view->render('block/top_girl_city.phtml');
    }
}