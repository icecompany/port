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
            'general_limit' => '',
            'contracts_v2-show_full_manager_fio' => 0,
            'contracts_v2-position_total' => 1,
            'contracts_v2-filter_doc_status' => 0,
            'contracts_v2-filter_currency' => 1,
            'contracts_v2-filter_manager' => 1,
            'contracts_v2-filter_activity' => 1,
            'contracts_v2-filter_rubric' => 1,
            'contracts_v2-filter_status' => 1,
            'contracts_v2-filter_cwt' => 1,
            'contracts_v2-filter_country' => 0,
            'contracts_v2-filter_info_catalog' => 0,
            'contracts_v2-filter_logo_catalog' => 0,
            'contracts_v2-filter_pvn_1' => 0,
            'contracts_v2-filter_pvn_1a' => 0,
            'contracts_v2-filter_pvn_1b' => 0,
            'contracts_v2-filter_pvn_1v' => 0,
            'contracts_v2-filter_pvn_1g' => 0,
            'contracts_v2-column_parent' => 1,
            'contracts_v2-column_manager' => 1,
            'contracts_v2-column_doc_status' => 0,
            'contracts_v2-column_id' => 0,
            'building-show_city' => 0,
            'building-show_city_fact' => 0,
            'building-show_addr' => 0,
            'building-show_addr_fact' => 0,
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
        //Автодополнение новых значений параметров (которые появляются по мере разработки и не заданы пользователем) из значений по умолчанию
        foreach ($this->default as $param => $value) {
            if (!isset($params[$param])) $params[$param] = $value;
        }

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