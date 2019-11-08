<?php
use Joomla\CMS\MVC\Controller\AdminController;
use Joomla\CMS\MVC\Model\ListModel;

defined('_JEXEC') or die;

class ProjectsControllerContracts_v2 extends AdminController
{
    public function getModel($name = 'Contracts_v2', $prefix = 'ProjectsModel', $config = array())
    {
        return parent::getModel($name, $prefix, array('ignore_request' => true));
    }

    public function export(): void
    {
        $model = ListModel::getInstance('Contracts_v2', 'ProjectsModel');
        $model->export();
    }
}
