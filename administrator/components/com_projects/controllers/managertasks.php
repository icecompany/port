<?php
use Joomla\CMS\MVC\Controller\AdminController;
use Joomla\CMS\MVC\Model\ListModel;

defined('_JEXEC') or die;

class ProjectsControllerManagertasks extends AdminController
{
    public function getModel($name = 'Managertasks', $prefix = 'ProjectsModel', $config = array())
    {
        return parent::getModel($name, $prefix, array('ignore_request' => true));
    }

    public function export(): void
    {
        $model = ListModel::getInstance($name = 'Managertasks', $prefix = 'ProjectsModel', $config = array());
        JLoader::discover('PHPExcel', JPATH_LIBRARIES);
        JLoader::register('PHPExcel', JPATH_LIBRARIES . '/PHPExcel.php');
        $xls = $model->export();
        header("Expires: Mon, 1 Apr 1974 05:00:00 GMT");
        header("Last-Modified: " . gmdate("D,d M YH:i:s") . " GMT");
        header("Cache-Control: no-cache, must-revalidate");
        header("Pragma: public");
        header("Content-type: application/vnd.ms-excel");
        header("Content-Disposition: attachment; filename=Stat.xls");
        $objWriter = PHPExcel_IOFactory::createWriter($xls, 'Excel5');
        if ($objWriter->save('php://output')) {
            header('Set-Cookie: fileLoading=true');
            $this->setRedirect("index.php?option=com_projects&view=managertasks");
            $this->redirect();
        }
        jexit();
    }
}
