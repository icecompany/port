<?php
use Joomla\CMS\MVC\Controller\AdminController;
use Joomla\CMS\MVC\Model\ListModel;
defined('_JEXEC') or die;

class ProjectsControllerReports extends AdminController
{
    public function exportxls(): void
    {
        $model = ListModel::getInstance('Reports', 'ProjectsModel');
        $model->exportToExcel();
        jexit();
    }

    public function refresh()
    {
        $this->setRedirect($_SERVER['HTTP_REFERER'])->redirect();
        jexit();
    }
}
