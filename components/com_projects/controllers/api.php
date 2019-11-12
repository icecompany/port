<?php
use Joomla\CMS\MVC\Controller\BaseController;

defined('_JEXEC') or die;

class ProjectsControllerApi extends BaseController
{
    public function getModel($name = 'Api', $prefix = 'ProjectsModel', $config = array())
    {
        return parent::getModel($name, $prefix, $config);
    }

    public function getExhibitors()
    {
        $model = $this->getModel();
        $exhibitors = $model->getExhibitors();
        exit(json_encode($exhibitors));
    }

    public function getCities()
    {
        $model = $this->getModel();
        $cities = $model->getCities();
        exit(json_encode($cities));
    }

    public function getSalt()
    {
        $model = $this->getModel();
        $salt = $model->getSalt();
        exit(json_encode(array('salt' => $salt)));
    }

    public function registerUser()
    {
        $model = $this->getModel();
        $uid = $model->registerUser();
        exit(json_encode(array('result' => 'ok', 'company_id' => $uid)));
    }
}
