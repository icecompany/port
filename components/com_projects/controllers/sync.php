<?php
use Joomla\CMS\MVC\Controller\BaseController;

defined('_JEXEC') or die;

class ProjectsControllerSync extends BaseController
{
    public function getModel($name = 'Sync', $prefix = 'ProjectsModel', $config = array())
    {
        return parent::getModel($name, $prefix, $config);
    }

    public function managerStat()
    {
        $model = $this->getModel();
        $model->saveStatuses();
        jexit();
    }
}
