<?php

class Zend_View_Helper_ShowNewMassages
{
    public function ShowNewMassages()
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

        $view->massages = Application_Model_Kernel_Mass::getList('','mass.id BY DESC ', true, true, false, 1, false, false, 3, true);

        return $view->render('block/index/new_massages.phtml');
    }
}