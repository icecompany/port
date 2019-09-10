<?php
defined('_JEXEC') or die;

use Joomla\CMS\MVC\Model\AdminModel;

class ProjectsModelSetting extends AdminModel
{
    public function __construct($config = array())
    {
        $input = JFactory::getApplication()->input;

        //Настройки по умолчанию
        $this->default = array(
            'contracts_v2-show_full_manager_fio' => 0,
            'contracts_v2-filter_doc_status' => 0,
            'contracts_v2-filter_currency' => 1,
            'contracts_v2-filter_manager' => 1,
            'contracts_v2-filter_activity' => 1,
            'contracts_v2-filter_rubric' => 1,
            'contracts_v2-filter_status' => 1,
        );
        $this->tab = $input->getString('tab', 'general');
        parent::__construct($config);
    }

    public function getTable($name = 'Settings', $prefix = 'TableProjects', $options = array())
    {
        return JTable::getInstance($name, $prefix, $options);
    }

    public function getItem($pk = null)
    {
        $item = parent::getItem(array('userID' => JFactory::getUser()->id));
        if ($item->id == null) {
            return array_merge(array('id' => null), $this->default);
        }
        $params = array_merge(array('id' => $item->id), $item->params);

        return $params;
    }

    public function save($data)
    {
        $arr = array(
            'id' => $_POST['jform']['id'] ?? null,
            'userID' => JFactory::getUser()->id,
            'params' => json_encode($data)
        );
        return parent::save($arr);
    }

    public function delete(&$pks)
    {
        $row = $this->getItem($pks);
        if ($row->id == null) return true;
        return parent::delete($pks);
    }

    public function getForm($data = array(), $loadData = true)
    {
        $form = $this->loadForm(
            $this->option.'.setting', 'setting', array('control' => 'jform', 'load_data' => $loadData)
        );
        if (empty($form))
        {
            return false;
        }
        return $form;
    }

    protected function loadFormData()
    {
        $data = JFactory::getApplication()->getUserState($this->option.'.edit.setting.data', array());
        if (empty($data))
        {
            $data = $this->getItem();
        }

        return $data;
    }

    protected function prepareTable($table)
    {
        $nulls = array('params'); //Поля, которые NULL

        foreach ($nulls as $field) {
            if (!strlen($table->$field)) $table->$field = NULL;
        }
        parent::prepareTable($table);
    }

    public function publish(&$pks, $value = 1)
    {
        return parent::publish($pks, $value);
    }

    public function getTab()
    {
        return $this->tab;
    }

    private $default, $tab;
}