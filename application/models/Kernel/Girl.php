<?php

class Application_Model_Kernel_Girl extends Application_Model_Kernel_Page
{

    protected $id;
    protected $salon_id;
    protected $age;

    private $idGallery;
    private $_gallery = null;

    private $salon = null;

    public function __construct($id, $idPage, $idRoute, $idContentPack,
                                $pageEditDate, $pageStatus, $position, $salon_id, $idGallery, $age)
    {
        parent::__construct($idPage, $idRoute, $idContentPack, $pageEditDate, $pageStatus, self::TYPE_GIRL, $position);
        $this->id = $id;
        $this->_idContentPack = $idContentPack;
        $this->salon_id = $salon_id;
        $this->idGallery = $idGallery;
        $this->age = (int)$age;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getSalonId()
    {
        return $this->salon_id;
    }

    public function setSalonId($id)
    {
        $this->salon_id = (int)$id;

        return $this;
    }

    public function getIdGallery()
    {
        return $this->idGallery;
    }

    public function setIdGallery($idGallery)
    {
        $this->idGallery = $idGallery;
    }

    public function getAge()
    {
        return $this->age;
    }

    public function setAge($age)
    {
        $this->age = $age;

        return $this;
    }

    public function validate()
    {
        $e = new Application_Model_Kernel_Exception();
        if (is_null($this->_contentManager)) {
            throw new Exception(self::ERROR_CONTENT_MANAGER_IS_NOT_DEFINED);
        }
        $this->_contentManager->validate($e);
        if ((bool)$e->current())
            throw $e;
    }

    private static function getSelf(stdClass &$data)
    {
        return new self($data->id,
            $data->idPage, $data->idRoute, $data->idContentPack,
            $data->pageEditDate, $data->pageStatus, $data->position,
            $data->salon_id, $data->idGallery, $data->age
        );
    }

    public static function getList($order, $orderType, $content, $route, $searchName, $status, $page, $onPage, $limit, $group = true, $wher = false, $area = false, $nextorder = false)
    {
        $db = Zend_Registry::get('db');
        $select = $db->select()->from('girls');
        $select->join('pages', 'pages.idPage = girls.idPage');

        if ($route) {
            $select->join('routing', 'pages.idRoute = routing.idRoute');
        }
        if ($content) {
            $select->join('content', 'content.idContentPack = pages.idContentPack');
            $select->where('content.idLanguage = ?', Kernel_Language::getCurrent()->getId());
            if ($searchName) {
                $select->join('fields', "fields.idContent = content.idContent AND fields.fieldName = 'name'");
                $select->where('fields.fieldText LIKE ?', $searchName);
            }
        }
        $select->where('pages.pageType = ?', self::TYPE_GIRL);
        if ($wher) {
            $select->where($wher);
        }
        if ($order && $orderType) {
            if ($order == 'BY' && $orderType == 'RAND') {
                $select->order(new Zend_Db_Expr('RAND()'));
            } else {
                $select->order($order . ' ' . $orderType);
            }
        } else {
            if (!$nextorder) {
                $select->order('pages.idPage DESC');
            }
        }
        if ($nextorder) {
            $select->order($nextorder);
        }
        if ($status !== false)
            $select->where('pages.pageStatus = ?', $status);
        if ($group !== false)
            $select->group('girls.id');
        if ($limit !== false)
            $select->limit($limit);

        $cachemanager = Zend_Registry::get('cachemanager');
        $cache = $cachemanager->getCache('girls');

        if (($return = $cache->load(md5($select->assemble()) . "_" . (int)$onPage . "_" . (int)$page)) !== false) {
            return $return;
        } else {
            $return = new stdClass();
            if ($page !== false) {
                $paginator = Zend_Paginator::factory($select);
                $paginator->setItemCountPerPage($onPage);
                $paginator->setPageRange(5);
                $paginator->setCurrentPageNumber($page);
                $return->paginator = $paginator;
            } else {
                $return->paginator = $db->fetchAll($select);
            }
            $return->data = array();
            $i = 0;
            foreach ($return->paginator as $projectData) {
                $return->data[$i] = self::getSelf($projectData);
                if ($route) {
                    $url = new Application_Model_Kernel_Routing_Url($projectData->url);
                    $defaultParams = new Application_Model_Kernel_Routing_DefaultParams($projectData->defaultParams);
                    $route = new Application_Model_Kernel_Routing($projectData->idRoute, $projectData->type, $projectData->name, $projectData->module, $projectData->controller, $projectData->action, $url, $defaultParams, $projectData->routeStatus);
                    $return->data[$i]->setRoute($route);
                }
                if ($content) {
                    $contentLang = new Application_Model_Kernel_Content_Language($projectData->idContent, $projectData->idLanguage, $projectData->idContentPack);
                    $contentLang->setFieldsArray(Application_Model_Kernel_Content_Fields::getFieldsByIdContent($projectData->idContent));
                    $return->data[$i]->setContent($contentLang);
                }
                $i++;
            }
            $cache->save($return);
        }

        return $return;
    }

    public function save()
    {
        $db = Zend_Registry::get('db');
        try {
            $db->beginTransaction();
            $insert = is_null($this->_idPage);
            $this->savePageData(); //сохраняем даные страницы
            $data = array(
                'idPage' => $this->getIdPage(),
                'salon_id' => $this->salon_id,
                'idGallery' => $this->idGallery,
                'age' => (int)$this->age
            );
            if ($insert) {
                $this->_gallery = new Application_Model_Kernel_Gallery(null, time(), time(), 0);
                $this->_gallery->save();
                $this->_idGallery = $this->_gallery->getId();
                $data['idGallery'] = $this->_idGallery;

                $db->insert('girls', $data);
                $this->id = $db->lastInsertId();
            } else {
                $db->update('girls', $data, 'id = ' . intval($this->id));
            }
            $db->commit();
            $this->clearCache();
        } catch (Exception $e) {
            $db->rollBack();
            Application_Model_Kernel_ErrorLog::addLogRow(Application_Model_Kernel_ErrorLog::ID_SAVE_ERROR, $e->getMessage(), ';product.php');
            throw new Exception($e->getMessage());
        }
    }

    public function delete()
    {
        $db = Zend_Registry::get('db');
        $db->delete('girls', "girls.idPage = {$this->_idPage}");
        $this->deletePage();
    }

    public static function getByIdPage($idPage)
    {
        $idPage = intval($idPage);

        $db = Zend_Registry::get('db');
        $select = $db->select()->from('girls');
        $select->join('pages', 'girls.idPage = pages.idPage');
        $select->where('pages.idPage = ?', $idPage);
        $select->limit(1);
        if (($productData = $db->fetchRow($select)) !== false) {
            return self::getSelf($productData);
        } else {
            throw new Exception(self::ERROR_INVALID_ID);
        }
    }

    public function getGallery()
    {
        if (is_null($this->_gallery)) {
            $this->_gallery = Application_Model_Kernel_Gallery::getById($this->idGallery);
        }

        return $this->_gallery;
    }

    public function show()
    {
        $this->_pageStatus = self::STATUS_SHOW;
        $this->savePageData();
        $this->clearCache();
    }

    public function hide()
    {
        $this->_pageStatus = self::STATUS_HIDE;
        $this->savePageData();
        $this->clearCache();
    }

    public static function getById($id)
    {
        $id = (int)$id;
//		$cachemanager = Zend_Registry::get('cachemanager');
//		$cache = $cachemanager->getCache('department');
//		if (($project = $cache->load($idProject)) !== false) {
//			return $project;
//		} else {
        $db = Zend_Registry::get('db');
        $select = $db->select()->from('girls');
        $select->join('pages', 'girls.idPage = pages.idPage');
        $select->where('id = ?', $id);
        $select->limit(1);
        if (($productData = $db->fetchRow($select)) !== false) {
            return self::getSelf($productData);
        } else {
            throw new Exception(self::ERROR_INVALID_ID);
        }
//		}
    }

    public function getSalon()
    {
        if (is_null($this->salon)) {
            $this->salon = Application_Model_Kernel_Salon::getById($this->salon_id);
        }

        return $this->salon;
    }

    public function setPath($data)
    {
        $path = Application_Model_Kernel_TextRedactor::makeTranslit($data->content[1]["name"]);
        $this->getRoute()->setUrl('/girls/' . $path . '-%id%.html');
    }

    public function updatePath()
    {
        $path = $this->getRoute()->getUrl();
        $path = str_replace('%id%', $this->id, $path);

        $this->getRoute()->setUrl($path);
        $this->getRoute()->save();
    }

    public function clearCache()
    {
        $cachemanager = Zend_Registry::get('cachemanager');
        $cache = $cachemanager->getCache('girls');
        foreach ($cache->getIds () as $k=>$v) {
            $cache->remove($v);
        }
    }

    public function path()
    {
        return Kernel_City::getUrlForLink($this->getSalon()->getCity()).$this->getRoute()->getUrl();
    }
}