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

    public function assignToMe()
    {
        $ids = $this->input->get('cid');
        $model = $this->getModel('Contract');
        foreach ($ids as $id)
        {
            $item = $model->getItem($id);
            $data['id'] = $id;
            $data['managerID'] = JFactory::getUser()->id;
            $data['status'] = $item->status;
            $model->save($data);
        }
        $this->setMessage(JText::sprintf('COM_PROJECT_TASK_CONTRACTS_ASSIGNED'));
        $this->setRedirect("index.php?option=com_projects&view=contracts_v2");
        $this->redirect();
        jexit();
    }
}
