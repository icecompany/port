<?php
defined('_JEXEC') or die;
use Joomla\CMS\MVC\Model\AdminModel;

class ProjectsModelCtritem extends AdminModel {
    public function getTable($name = 'Ctritems', $prefix = 'TableProjects', $options = array())
    {
        return JTable::getInstance($name, $prefix, $options);
    }

    public function getItem($pk = null)
    {
        $item = parent::getItem($pk);
        if ($item->id == null) {
            $contractID = JFactory::getApplication()->getUserStateFromRequest('com_projects.contractID', 'contractID', 0);
            if ($contractID > 0) $item->contractID = $contractID;
            $itemID = JFactory::getApplication()->getUserStateFromRequest('com_projects.itemID', 'itemID', 0);
            if ($itemID > 0) $item->itemID = $itemID;
            $columnID = JFactory::getApplication()->getUserStateFromRequest('com_projects.columnID', 'columnID', 0);
            if ($columnID > 0) $item->columnID = $columnID;
        }
        else
        {
            $itemID = $item->itemID;
            $contractID = $item->contractID;
        }
        $item->title = sprintf("%s - %s", $this->getPriceItemTitle($itemID), $this->getContractNumber($contractID));
        $item->factor = ($item->id != null) ? 100 - ($item->factor * 100) : 0;
        return $item;
    }

    public function delete(&$pks)
    {
        $row = $this->getItem($pks);
        if ($row->id == null) return true;
        return parent::delete($pks);
    }

    public function save($data)
    {
        if ($data['need_check'] == 1) {
            $arr = array();
            $arr['ctrItemId'] = $data['id'] ?? null;
            $arr['columnID'] = $data['columnID'];
            $arr['value'] = $data['value'];
            $arr['factor'] = 1 - $data['factor'] ?? 0 / 100;
            $arr['managerID'] = JFactory::getUser()->id;
            $arr['is_new'] = ($data['id'] != null) ? 0 : 1;
            $data['factor'] = 1 - $data['factor'] ?? 0 / 100;
            $data['markup'] = ((float) $data['markup'] <= 0) ? null : $data['markup'];
        }
        if ($data['value'] <= 0)
        {
            if ($data['id'] != null) $this->delete($data['id']);
            if ($data['value2'] == 0) $data['value2'] = null;
            return true;
        }
        $data['managerID'] = JFactory::getUser()->id;
        return parent::save($data);
    }

    /**
     * Возвращает название пункта прайса
     * @param int $itemID ID пункта прайса
     * @return string
     * @since 1.2.2.8
     */
    private function getPriceItemTitle(int $itemID = 0): string
    {
        if ($itemID == 0) return '';
        $im = AdminModel::getInstance('Item', 'ProjectsModel');
        $item = $im->getItem($itemID);
        return $item->title_ru;
    }

    /**
     * Возвращает номер договора
     * @param int $contractID ID сделки
     * @return string
     * @since 1.2.2.8
     */
    private function getContractNumber(int $contractID = 0): string
    {
        $im = AdminModel::getInstance('Contract', 'ProjectsModel');
        $item = $im->getItem($contractID);
        return ($item->number != null) ? JText::sprintf('COM_PROJECTS_TITLE_CONTRACT_WITH_NUMBER', $item->number) : JText::sprintf('COM_PROJECTS_TITLE_CONTRACT_WITHOUT_NUMBER');
    }

    public function getForm($data = array(), $loadData = true)
    {
        $form = $this->loadForm(
            $this->option.'.ctritem', 'ctritem', array('control' => 'jform', 'load_data' => $loadData)
        );
        if (empty($form))
        {
            return false;
        }
        return $form;
    }

    protected function loadFormData()
    {
        $data = JFactory::getApplication()->getUserState($this->option.'.edit.ctritem.data', array());
        if (empty($data))
        {
            $data = $this->getItem();
        }

        return $data;
    }

    protected function prepareTable($table)
    {
    	$nulls = array('markup', 'value2', 'fixed', 'arrival'); //Поля, которые NULL

	    foreach ($nulls as $field)
	    {
		    if (!strlen($table->$field)) $table->$field = NULL;
    	}
        parent::prepareTable($table);
    }

    public function publish(&$pks, $value = 1)
    {
        return parent::publish($pks, $value);
    }
}