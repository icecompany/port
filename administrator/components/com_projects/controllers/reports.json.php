<?php
use Joomla\CMS\MVC\Controller\AdminController;
use Joomla\CMS\MVC\Model\ListModel;
defined('_JEXEC') or die;

class ProjectsControllerReports extends AdminController
{
    public function execute($task)
    {
        $model = ListModel::getInstance('Reports', 'ProjectsModel');
        $items = $model->getItems();
        $fp = fopen('php://output', 'w');
        foreach ($items as $item) {
            fputcsv($fp, $item);
        }
        fclose($fp);
        //jexit(var_dump('ok'));
    }

    public function refresh()
    {
        $this->setRedirect($_SERVER['HTTP_REFERER'])->redirect();
        jexit();
    }
}
