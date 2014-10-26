<?php
class Admin_DiscountController extends Zend_Controller_Action
{

    public function preDispatch()
    {
        if (!Application_Model_Admin_Admin::isAuthorized())
            $this->_redirect($this->view->url(array (), 'admin-login'));
        else
            $this->view->blocks = (object)array ('menu' => true);
        $this->view->add         = false;
        $this->view->back        = false;
        $this->view->breadcrumbs = new Application_Model_Kernel_Breadcrumbs();
        $this->view->page        = !is_null($this->_getParam('page')) ? $this->_getParam('page') : 1;

        $this->view->salon_id = (int)$this->_getParam('salon_id');
        $this->view->headTitle()->append('Акции');
    }

    public function indexAction()
    {
        $this->view->add = (object)array(
            'link' => $this->view->url(array('salon_id'=>$this->view->salon_id), 'admin-discount-add'),
            'alt'  => 'Добавить',
            'text' => 'Добавить'
        );

        $this->view->page = (int)$this->_getParam('page');

        $this->view->breadcrumbs->add('Список акций', '');
        $this->view->headTitle()->append('Список акций');
        $this->view->items = Application_Model_Kernel_Discount::getList(
            false, false, true,
            true, false, false,
            $this->view->page, 15, false,
            true, ' discount.salon_id = '.(int)$this->view->salon_id);
    }

    public function addAction()
    {
        $this->view->langs = Kernel_Language::getAll();
        $this->view->idPage = null;
        $this->view->tinymce = true;
        $this->view->back = true;
        $this->view->edit = false;
        $this->view->idPhoto1 = 0;

        if ($this->getRequest()->isPost()) {
            $data = (object)$this->getRequest()->getPost();
            try {
                $this->view->idPhoto1 = (int)$data->idPhoto1;
                $this->view->photo1 = Application_Model_Kernel_Photo::getById($this->view->idPhoto1);

                $url = new Application_Model_Kernel_Routing_Url("/skidki/".$data->url.'.html');
                $defaultParams = new Application_Model_Kernel_Routing_DefaultParams();
                $route = new Application_Model_Kernel_Routing(null, Application_Model_Kernel_Routing::TYPE_ROUTE, '~public', 'default', 'discount', 'show', $url, $defaultParams, Application_Model_Kernel_Routing::STATUS_ACTIVE);

                $content = array();
                $i = 0;
                foreach ($this->view->langs as $lang) {
                    $content[$i] = new Application_Model_Kernel_Content_Language(null, $lang->getId(), null);
                    foreach ($data->content[$lang->getId()] as $k => $v)
                        $content[$i]->setFields($k, $v);
                    $i++;
                }
                $contentManager = new Application_Model_Kernel_Content_Manager(null, $content);
                $this->view->item = new Application_Model_Kernel_Discount(null, $this->view->salon_id, $this->view->idPhoto1,
                    null, null, null,
                    time(), Application_Model_Kernel_Page_ContentPage::STATUS_SHOW, 0
                );

                $this->view->item->setContentManager($contentManager);
                $this->view->item->setRoute($route);
                $this->view->item->validate($data);
                $this->view->item->save();

                $this->_redirect($this->view->url(array('page' => 1, 'salon_id'=>$this->view->salon_id), 'admin-discount-index'));
            } catch (Application_Model_Kernel_Exception $e) {
                $this->view->ShowMessage($e->getMessages());
            } catch (Exception $e) {
                $this->view->ShowMessage($e->getMessage());
            }
        }

        $this->view->breadcrumbs->add('Добавить вид', '');
        $this->view->headTitle()->append('Добавить');
    }

    public function editAction()
    {
        $this->view->langs = Kernel_Language::getAll();
        $this->view->back = true;
        $this->_helper->viewRenderer->setScriptAction('add');
        $this->view->tinymce = true;
        $this->view->edit = true;
        $this->view->item = Application_Model_Kernel_Discount::getById((int)$this->_getParam('id'));

        $this->view->idPhoto1 = $this->view->item->getIdPhoto1();

        $getContent = $this->view->item->getContentManager()->getContent();
        $this->view->idPage = $this->view->item->getIdPage();
        if ($this->getRequest()->isPost()) {
            $data = (object)$this->getRequest()->getPost();
            try {
                Application_Model_Kernel_Content_Fields::setFieldsForModel($data->content, $getContent, $this->view->item);

                $this->view->idPhoto1 = (int)$data->idPhoto1;
                $this->view->photo1 = Application_Model_Kernel_Photo::getById($this->view->idPhoto1);
                $this->view->item->setIdPhoto1($this->view->idPhoto1);

                $this->view->item->getRoute()->setUrl('/skidki/'.$data->url.'.html');

                $this->view->item->validate($data);
                $this->view->item->save();

                $this->_redirect($this->view->url(array('page' => 1,'salon_id'=>$this->view->salon_id), 'admin-discount-index'));
            } catch (Application_Model_Kernel_Exception $e) {
                $this->view->ShowMessage($e->getMessages());
            } catch (Exception $e) {
                $this->view->ShowMessage($e->getMessage());
            }
        } else {
            $this->view->photo1 = Application_Model_Kernel_Photo::getById($this->view->idPhoto1);

            $_POST['url'] = mb_substr(Application_Model_Kernel_Routing::getById($this->view->item->getIdRoute())->getUrl(), 1);
            $_POST['url'] = mb_substr($_POST['url'], 7, -5);

            $_POST['content'] = $this->view->item->getContentManager()->getContents();
            foreach ($this->view->langs as $lang) {
                if (isset($_POST['content'][$lang->getId()]))
                    foreach ($_POST['content'][$lang->getId()] as $value)
                        $_POST['content'][$lang->getId()][$value->getFieldName()] = $value->getFieldText();
            }
        }
        $this->view->breadcrumbs->add('Редактировать', '');
        $this->view->headTitle()->append('Редактировать');
    }

    public function deleteAction()
    {
        $this->_helper->viewRenderer->setNoRender();
        $this->_helper->layout()->disableLayout();
        $this->view->item = Application_Model_Kernel_Discount::getById((int)$this->_getParam('id'));
        if ($this->view->item) {
            try {
                $this->view->item->delete();
            } catch (Application_Model_Kernel_Exception $e) {
                $this->view->ShowMessage($e->getMessages());
            } catch (Exception $e) {
                $this->view->ShowMessage($e->getMessage());
            }
        }
        $this->_redirect($this->view->url(array('page' => 1,'salon_id'=>$this->view->salon_id), 'admin-discount-index'));
    }
}