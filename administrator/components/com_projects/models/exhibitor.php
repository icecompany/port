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

    public function getItem($pk = null)
    {
        $table = $this->getTable();
        $id = JFactory::getApplication()->input->get('id', 0);
        if ($id != 0)
        {
            $table->load($id);
        }
        $item = parent::getItem($pk);
        if ($id > 0) {
            $parent = ProjectsHelper::getExhibitorParent($id);
            if ($parent > 0) $item->parentID = $parent;
            $children = ProjectsHelper::getExhibitorChildren($id);
            if (!empty($children)) $item->children = $children;
            $item->hidden_city_id = $item->regID;
            $item->hidden_city_title = ProjectsHelper::getCityTitle($item->regID);
            $item->hidden_city_fact_id = $item->regID_fact;
            $item->hidden_city_fact_title = ProjectsHelper::getCityTitle($item->regID_fact);
            if ($item->parentID !== null && $item->parentID > 0) {
                $item->hidden_parent_id = $item->parentID;
                $item->hidden_parent_title = ProjectsHelper::getExhibitorTitle($item->parentID);
            }
        }
        if ($id == 0) {
            $tip = JFactory::getApplication()->input->getString('layout', null);
            if ($tip == 'contractor') {
                $item->is_contractor = '1';
            }
            if ($tip == 'edit') {
                $item->is_contractor = '0';
            }
        }
        $item->title = ProjectsHelper::getExpTitle($item->title_ru_short, $item->title_ru_full, $item->title_en);
        $item->activities = $this->getActivities();
        $where = array('exbID' => $item->id);
        $bank = AdminModel::getInstance('Bank', 'ProjectsModel')->getItem($where);
        $address = AdminModel::getInstance('Address', 'ProjectsModel')->getItem($where);
        unset($item->_errors, $bank->exbID, $bank->id, $bank->_errors, $address->exbID, $address->id, $address->_errors);
        return (object)array_merge((array)$item, (array)$bank, (array)$address);
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
            ->order("`c`.`is_capital` DESC, `c`.`name`");
        if ($id == 0) {
            $query->where("`c`.`name` LIKE {$search}");
        }
        if ($id > 0) {
            $query->Where("`c`.`id` = {$id}");
        }
        $result = $db->setQuery($query)->loadObjectList();

        $options = array();

        if ($result) {
            foreach ($result as $p) {
                $name = sprintf("%s (%s, %s)", $p->city, $p->region, $p->country);
                $options[] = array('id' => $p->id, 'name' => $name);
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

    /**
     * Возвращает массив со ссылками на дочерние компании экспонента
     * @return array
     * @since 1.1.4.0
     */
    public function getChildren(): array
    {
        $item = parent::getItem();
        $result = array();
        if ($item->id == null) return $result;
        $items = ProjectsHelper::getExhibitorChildren($item->id, true);
        if (empty($items)) return $result;
        $return = base64_encode("index.php?option=com_projects&view=exhibitor&layout=edit&id={$item->id}");
        foreach ($items as $exhibitor) {
            $arr = array();
            $title = ProjectsHelper::getExpTitle($exhibitor['title_ru_short'], $exhibitor['title_ru_full'], $exhibitor['title_en']);
            $url = JRoute::_("index.php?option=com_projects&amp;task=exhibitor.edit&amp;id={$exhibitor['exhibitorID']}&amp;return={$return}");
            $arr['id'] = $exhibitor['exhibitorID'];
            $arr['link'] = JHtml::link($url, $title, array('target' => '_blank'));
            $url = JRoute::_("index.php?option=com_projects&amp;view=contracts&amp;exhibitorID={$exhibitor['exhibitorID']}&amp;return={$return}");
            $arr['contracts'] = JHtml::link($url, JText::sprintf('COM_PROJECTS_BLANK_EXHIBITOR_HISTORY'), array('target' => '_blank'));
            $result[] = $arr;
        }
        return $result;
    }

    /**
     * Возвращает виды деятельности у экспонента
     * @return array
     * @since 1.1.2.5
     */
    private function getActivities()
    {
        $item = parent::getItem();
        $db = $this->getDbo();
        if ($item->id == null) return array();
        $exbID = $item->id;
        $query = $db->getQuery(true);
        $query
            ->select('`actID`')
            ->from($db->quoteName('#__prj_exp_act'))
            ->where($db->quoteName('exbID') . " = " . $db->quote($exbID));
        $activities = $db->setQuery($query)->loadColumn();
        return $activities;
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
        $nulls = array('tip', 'title_ru_short', 'title_ru_full', 'title_en', 'comment', 'user_id'); //Поля, которые NULL
        foreach ($nulls as $field) {
            if (!strlen($table->$field)) $table->$field = NULL;
        }
        parent::prepareTable($table);
    }

    public function getScript()
    {
        return 'administrator/components/' . $this->option . '/models/forms/exhibitor.js';
    }

    public function getRegionScript()
    {
        return 'administrator/components/' . $this->option . '/models/forms/regions.js';
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
        $s4 = $this->saveActivities($data['activities'] ?? array());
        if ($data['parentID'] != '') {
            $this->saveParent((int) $data['exbID'], (int) $data['parentID']);
        }
        else {
            $this->saveParent($data['exbID'], 0);
        }
        $this->saveChildren((int) $data['exbID'], $data['children'] ?? array());
        return $s1 && $s2 && $s3 && $s4;
    }

    /**
     * Сохраняет родителя экспонента
     * @param int $exhibitorID ID экспонента
     * @param int $parentID ID родителя. Если 0 - удаляет родителя
     * @since 1.1.2.8
     */
    public function saveParent(int $exhibitorID, int $parentID = 0): void
    {
        if ($exhibitorID == 0 || $exhibitorID == $parentID) return;
        $pm = AdminModel::getInstance('Exparent', 'ProjectsModel');
        $item = $pm->getItem(array('exhibitorID' => $exhibitorID));
        if ($parentID > 0) {
            $arr = array();
            $arr['id'] = $item->id;
            $arr['exhibitorID'] = $exhibitorID;
            $arr['parentID'] = $parentID;
            $pm->save($arr);
        }
        else {
            if ($item->id != null) $pm->delete($item->id);
        }
    }

    /**
     * Обновляет информацию о дочерних экспонентах
     * @param int $parentID ID родителя
     * @param array $children массив с ID дочерних экспонентов
     * @since 1.1.2.8
     */
    public function saveChildren(int $parentID, array $children): void
    {
        $pm = AdminModel::getInstance('Exparent', 'ProjectsModel');
        $already = ProjectsHelper::getExhibitorChildren($parentID);
        if (!empty($children)) {
            foreach ($children as $exhibitor) {
                $item = $pm->getItem(array('exhibitorID' => $exhibitor, 'parentID' => $parentID));
                $arr = array();
                $arr['id'] = $item->id;
                $arr['exhibitorID'] = $exhibitor;
                $arr['parentID'] = $parentID;
                if (in_array($exhibitor, $already)) {
                    if (($key = array_search($exhibitor, $already)) !== false) unset($already[$key]);
                }
                $pm->save($arr);
            }
        }
        foreach ($already as $exhibitor) {
            $item = $pm->getItem(array('exhibitorID' => $exhibitor, 'parentID' => $parentID));
            if ($item->id != null) $pm->delete($item->id);
        }
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
     * Возвращает название подгружаемого слоя для редактирования компании
     * @return string
     * @throws Exception
     * @since 1.1.6.2
     */
    public function getLayout(): string
    {
        $id = JFactory::getApplication()->input->getInt('id', 0);
        $item = parent::getItem($id);
        return ($item->is_contractor == '0') ? 'edit' : 'contractor';
    }

    /**
     * Сохраняет запись в дочернюю таблицу видов деятельности.
     * @param array $activities массив с видами деятельности
     * @return  boolean True on success, False on error.
     * @since   1.1.2.5
     * @throws
     */
    private function saveActivities(array $activities): bool
    {
        $exbID = $this->getId();
        if (empty($activities)) return true;
        $already = $this->getActivities();
        $am = AdminModel::getInstance('Act', 'ProjectsModel');
        foreach ($activities as $act) {
            $arr = array();
            $pks = array('exbID' => $exbID, 'actID' => $act);
            $row = $am->getItem($pks);
            $arr['id'] = $row->id;
            $arr['exbID'] = $exbID;
            $arr['actID'] = $act;
            if (in_array($act, $already)) {
                if (($key = array_search($act, $already)) !== false) unset($already[$key]);
            } //Удаляем сделку из списка на удаление из таблицы делегатов
            $am->save($arr);
        }
        foreach ($already as $act) {
            $item = $am->getItem(array('exbID' => $exbID, 'actID' => $act));
            if ($item->id != null) $am->delete($item->id);
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