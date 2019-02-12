<?php
defined('_JEXEC') or die;

use Joomla\CMS\MVC\Model\AdminModel;

class ProjectsModelExhibitor extends AdminModel
{
    public function getTable($name = 'Exponents', $prefix = 'TableProjects', $options = array())
    {
        $this->addTablePath(JPATH_COMPONENT_ADMINISTRATOR . '/tables');
        return JTable::getInstance($name, $prefix, $options);
    }

    public function getRegion(int $id, string $search): array
    {
        $db =& $this->getDbo();
        $search = $db->q('%'.$db->escape($search).'%');
        $query = $db->getQuery(true);
        $query
            ->select("`c`.`id`, `c`.`name` as `city`, `r`.`name` as `region`, `s`.`name` as `country`")
            ->from('`#__grph_cities` as `c`')
            ->leftJoin('`#__grph_regions` as `r` ON `r`.`id` = `c`.`region_id`')
            ->leftJoin('`#__grph_countries` as `s` ON `s`.`id` = `r`.`country_id`')
            ->order("`c`.`is_capital` DESC, `c`.`name`")
            ->where("(`s`.`state` = 1 AND `c`.`name` LIKE {$search})");
        if ($id > 0) {
            $query->orWhere("`c`.`id` = {$id}");
        }
        $result = $db->setQuery($query)->loadObjectList();

        $options = array();

        if ($result) {
            foreach ($result as $p) {
                if (!isset($options[$p->region])) {
                    $options[$p->region] = array();
                }
                $name = sprintf("%s (%s, %s)", $p->city, $p->region, $p->country);
                $options[$p->region][$p->id] = $name;
            }
        }

        return $options;
    }

    /**
     * Возвращает текущих контактных лиц для экспонента
     * @return array
     * @since 1.3.0.9
     */
    public function getPersons(): array
    {
        $item = parent::getItem();
        if ($item->id == null) return array();
        $items = ProjectsHelper::getExhibitorPersons($item->id);
        $result = array();
        foreach ($items as $item) {
            $arr = array();
            $arr['id'] = $item->id;
            $arr['fio'] = $item->fio;
            $arr['post'] = $item->post;
            $arr['phone_work'] = $item->phone_work;
            $arr['phone_mobile'] = $item->phone_mobile;
            $arr['main'] = $item->main;
            $arr['email_clean'] = $item->email;
            $arr['comment'] = $item->comment;
            $arr['email'] = (!empty($item->email)) ? $item->email." / ".JHtml::link(JRoute::_("mailto:{$item->email}"), JText::sprintf('COM_PROJECTS_ACTION_WRITE')) : '';
            $arr['action'] = JRoute::_("index.php?option=com_projects&amp;task=person.edit&amp;id={$item->id}");
            $result[] = $arr;
        }
        return $result;
    }

    public function getItem($pk = null)
    {
        $table = $this->getTable();
        $id = JFactory::getApplication()->input->get('id', 0);
        if ($id != 0)
        {
            $table->load($id);
        }
        $item = parent::getItem($pk);
        $item->title = ProjectsHelper::getExpTitle($item->title_ru_short, $item->title_ru_full, $item->title_en);
        $where = array('exbID' => $item->id);
        $bank = AdminModel::getInstance('Bank', 'ProjectsModel')->getItem($where);
        $address = AdminModel::getInstance('Address', 'ProjectsModel')->getItem($where);
        if (mb_strpos($address->site, 'http://') === false && !empty($address->site)) $address->site = "http://".$address->site;
        unset($item->_errors, $bank->exbID, $bank->id, $bank->_errors, $address->exbID, $address->id, $address->_errors);
        return (object)array_merge((array)$item, (array)$bank, (array)$address);
    }

    public function getForm($data = array(), $loadData = true)
    {
        $form = $this->loadForm(
            $this->option . '.exhibitor', 'exhibitor', array('control' => 'jform', 'load_data' => $loadData)
        );
        if (empty($form)) {
            return false;
        }
        return $form;
    }

    protected function loadFormData()
    {
        $data = JFactory::getApplication()->getUserState($this->option . '.edit.exhibitor.data', array());
        if (empty($data)) {
            $data = $this->getItem();
        }

        return $data;
    }

    protected function prepareTable($table)
    {
        $nulls = array('tip', 'title_ru_short', 'title_ru_full', 'title_en', 'comment'); //Поля, которые NULL
        foreach ($nulls as $field) {
            if (!strlen($table->$field)) $table->$field = NULL;
        }
        parent::prepareTable($table);
    }

    public function getScript()
    {
        return 'administrator/components/' . $this->option . '/models/forms/exhibitor.js';
    }

    public function save($data)
    {
        if ($data['id'] != null) $old = parent::getItem($data['id']);
        $s1 = parent::save($data);
        $action = ($data['id'] != null) ? 'edit' : 'add';

        $data = $this->addId($data);

        $data['id'] = $data['bank_id'];

        ProjectsHelper::addEvent(array('action' => $action, 'section' => 'exhibitor', 'itemID' => $data['exbID'], 'params' => $data, 'old_data' => $old));

        unset($data['bank_id']);
        $s2 = $this->saveData('Bank', $data);
        $data['id'] = $data['address_id'];
        unset($data['address_id']);
        $s3 = $this->saveData('Address', $data);
        $s4 = $this->saveActivities();
        return $s1 && $s2 && $s3 && $s4;
    }

    public function delete(&$pks)
    {
        foreach ($pks as $pk) {
            $old = parent::getItem();
            ProjectsHelper::addEvent(array('action' => 'delete', 'section' => 'exhibitor', 'itemID' => $pk, 'old_data' => $old));
        }
        return parent::delete($pks);
    }

    /**
     * Возвращает историю участия экспонента в проектах
     * @return array
     * @since 1.2.6
     * @throws
     */
    public function getHistory(): array
    {
        $expID = JFactory::getApplication()->input->getInt('id', 0);
        if ($expID == 0) return array();
        $model = AdminModel::getInstance('History', 'ProjectsModel');
        $history = $model->getHistory($expID);
        return $history;
    }

    /**
     * Получает массив видов деятельности для текущего экспонента.
     * @return  array   Массив
     * @since   1.1.3
     * @throws
     */
    public function getActivities(): array
    {
        $exbID = $this->getId();
        $db =& $this->getDbo();
        $query = $db->getQuery(true);
        $query
            ->select('*')
            ->from($db->quoteName('#__prj_exp_act'))
            ->where($db->quoteName('exbID') . " = " . $db->quote($exbID));
        $activities = $db->setQuery($query)->loadAssocList();
        $db =& $this->getDbo();
        $query = $db->getQuery(true);
        $query
            ->select('*')
            ->from("`#__prj_activities`")
            ->order('title desc');
        $list = $db->setQuery($query)->loadAssocList();

        $result = array();
        foreach ($list as $item) {
            $arr = array();
            $arr['id'] = $item['id'];
            $arr['title'] = $item['title'];
            $arr['checked'] = false;
            if ($exbID > 0) {
                foreach ($activities as $activity) {
                    if ($activity['exbID'] == $exbID && $activity['actID'] == $item['id']) {
                        $arr['checked'] = true;
                        break;
                    }
                }
            }
            $result[] = $arr;
        }
        return $result;
    }

    /**
     * Сохраняет запись в дочернюю таблицу видов деятельности.
     * @return  boolean True on success, False on error.
     * @since   1.1.3
     * @throws
     */
    private function saveActivities(): bool
    {
        $exbID = $this->getId();
        $post = $_POST['jform']['act'];
        if (empty($post)) return true;
        $model = AdminModel::getInstance('Act', 'ProjectsModel');
        $table = $model->getTable();
        foreach ($post as $act => $value) {
            $pks = array('exbID' => $exbID, 'actID' => $act);
            $row = $model->getItem($pks);
            if ($row->id != null)
            {
                if ($value == '')
                {
                    $model->delete($row->id);
                }
            }
            else {
                if ($value == '1') {
                    $arr['exbID'] = $exbID;
                    $arr['actID'] = $act;
                    $arr['id'] = null;
                    $table->bind($arr);
                    $model->save($arr);
                }
            }
        }
        return true;
    }

    /**
     * Сохраняем запись в дочернюю таблицу (кроме видов деятельности).
     * @param   string $modelName Краткое название модели.
     * @param   array $data Массив с добавляемыми данными.
     * @return  boolean True on success, False on error.
     * @since   1.1.2
     * @throws
     */
    private function saveData(string $modelName, array $data): bool
    {
        $model = AdminModel::getInstance($modelName, 'ProjectsModel');
        $table = $model->getTable()->bind($data);
        $model->prepareTable($table);
        $result = (!$model->save($data)) ? false : true;
        if (!$result) JFactory::getApplication()->enqueueMessage($model->getError(), 'error');
        return $result;
    }

    /**
     * Добавляет в массив добавляемых элементов поле с id записи, если нужно обновить её в дочерней таблице,
     * А также поле с ID экспонента
     * @param   array $data Массив с добавляемыми данными
     * @return  array
     * @since   1.1.3
     * @throws
     */
    private function addId(array $data): array
    {
        $id = $this->getId();
        if ($id !== 0) $data['exbID'] = $id;
        $model = AdminModel::getInstance('Bank', 'ProjectsModel');
        $item = $model->getItem(array('exbID' => $id));
        if ($item->id != null)
        {
            $data['bank_id'] = $item->id;
        }
        $model = AdminModel::getInstance('Address', 'ProjectsModel');
        $item = $model->getItem(array('exbID' => $id));
        if ($item->id != null)
        {
            $data['address_id'] = $item->id;
        }
        return $data;
    }

    /**
     * Получает ИД записи в таблице
     * @return  integer
     * @since   1.1.2
     * @throws
     */
    private function getId(): int
    {
        $tmp = JFactory::getApplication()->input->getInt('id', 0);
        $insertID = $this->getTable()->getDbo()->insertid();
        return ($tmp == 0) ? $insertID : $tmp;
    }

}