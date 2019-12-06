<?php
defined('_JEXEC') or die;
use Joomla\CMS\MVC\Model\AdminModel;

class ProjectsModelTodo extends AdminModel {
    public function getTable($name = 'Todos', $prefix = 'TableProjects', $options = array())
    {
        return JTable::getInstance($name, $prefix, $options);
    }

    /**
     * Возвращает количество активных заданий у текущего пользователя на определённую дату
     * @param string $dat Дата в формате Y-m-d
     * @param int $uid ID пользоватедя. Если 0 - текущий
     * @return int Количество активных заданий или -1, если получена пустая дата
     * @since 1.0.2.0
     */
    public function getTodosCountOnDate(string $dat, int $uid = 0): int
    {
        if (empty($dat)) return -1;
        $dat = $this->_db->q($dat);
        $uid = ($uid != 0) ? $uid : JFactory::getUser()->id;
        $db =& $this->getDbo();
        $query = $db->getQuery(true);
        $query
            ->select("IFNULL(COUNT(*),0) as `cnt`")
            ->from("`#__prj_todos`")
            ->where("`managerID` = {$uid}")
            ->where("`dat` = {$dat}")
            ->where("`state` = 0");
        return $db->setQuery($query)->loadResult() ?? 0;
    }

    public function getItem($pk = null)
    {
        $item = parent::getItem($pk);
        $item->dat = $item->dat ?? date("Y-m-d", strtotime("+1 Weekday"));
        $item->managerID = $item->managerID ?? JFactory::getUser()->id;

        return $item;
    }

    public function getForm($data = array(), $loadData = true)
    {
        $form = $this->loadForm(
            $this->option.'.todo', 'todo', array('control' => 'jform', 'load_data' => $loadData)
        );
        if (empty($form))
        {
            return false;
        }
        $id = JFactory::getApplication()->input->get('id', 0);
        $user = JFactory::getUser();
        if ($id != 0 && (!$user->authorise('core.edit.state', $this->option . '.todo.' . (int) $id))
            || ($id == 0 && !$user->authorise('core.edit.state', $this->option)))
            $form->setFieldAttribute('state', 'disabled', 'true');
        if ($id == 0 && !ProjectsHelper::canDo('core.general'))
        {
            $form->setValue('managerID', JFactory::getUser()->id);
        }

        return $form;
    }

    protected function loadFormData()
    {
        $data = JFactory::getApplication()->getUserState($this->option.'.edit.todo.data', array());
        if (empty($data))
        {
            $data = $this->getItem();
        }

        return $data;
    }

    public function save($data)
    {
        if ($data['id'] == 0)
        {
            $data['userOpen'] = JFactory::getUser()->id;
            $data['dat_open'] = date("Y-m-d");
            if ($data['state'] == '1') $data['dat_close'] = JDate::getInstance()->toSql();
        }
        if ($data['id'] != 0 && $data['state'] == 1)
        {
            $data['userClose'] = JFactory::getUser()->id;
            $data['dat_close'] = date("Y-m-d H:i:s");
        }
        return parent::save($data);
    }

    /**
     * Закрывает задачу. Используется для асинхронного запроса из вьюшки со сделкой
     * @param bool $notify Закрыть уведомление
     * @since 1.2.9.6
     * @return boolean
     * @throws
     */
    public function close(bool $notify = false)
    {
        $data['userClose'] = JFactory::getUser()->id;
        $data['dat_close'] = date("Y-m-d H:i:s");
        $data['result'] = (!$notify) ? JFactory::getDbo()->escape(JFactory::getApplication()->input->getString('result')) : NULL;
        $data['id'] = JFactory::getDbo()->escape(JFactory::getApplication()->input->getInt('id', 0));
        $item = $this->getItem($data['id']);
        $group = $item->notify_group;
        $data['state'] = '1';
        if ($data['id'] == 0) return false;
        $s = parent::save($data);
        if ($group != null) {
            $db =& $this->getDbo();
            $query = $db->getQuery(true);
            $query
                ->update("`#__prj_todos`")
                ->set("`state` = 1")
                ->where("`is_notify` = 1")
                ->where("`notify_group` = {$group}");
            $db->setQuery($query)->execute();
        }
        return $s;
    }

    protected function prepareTable($table)
    {
    	$nulls = array('userClose', 'result', 'dat_close', 'notify_group'); //Поля, которые NULL
	    foreach ($nulls as $field)
	    {
		    if (!strlen($table->$field)) $table->$field = NULL;
    	}
        parent::prepareTable($table);
    }

    protected function canEditState($record)
    {
        $user = JFactory::getUser();

        if (!empty($record->id))
        {
            return $user->authorise('core.edit.state', $this->option . '.todo.' . (int) $record->id);
        }
        else
        {
            return parent::canEditState($record);
        }
    }

    /**
     * Перенос задач на определённый период ВПЕРЁД
     * @param array $ids массив с ID задач
     * @param string $period период
     * @since 1.2.0.8
     */
    public function toWeek(array $ids, string $period = "1 week"): void
    {
        if (empty($ids)) return;
        $ids = implode(', ', $ids);
        $db =& $this->getDbo();
        $query = $db->getQuery(true);
        $query
            ->update("`#__prj_todos`")
            ->set("`dat` = `dat` + INTERVAL {$period}")
            ->where("`id` IN ({$ids})");
        $db->setQuery($query)->execute();
    }

    /**
     * Отмечает уведомления прочитанными
     * @param array $ids массив с ID задач
     * @since 1.2.0.8
     */
    public function read(array $ids): void
    {
        if (empty($ids)) return;
        $ids = implode(', ', $ids);
        $db =& $this->getDbo();
        $query = $db->getQuery(true);
        $query
            ->update("`#__prj_todos`")
            ->set("`state` = 1")
            ->where("`id` IN ({$ids})");
        $db->setQuery($query)->execute();
    }

    public function getScript()
    {
        return 'administrator/components/' . $this->option . '/models/forms/todo.js';
    }
}