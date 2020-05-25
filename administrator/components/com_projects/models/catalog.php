<?php
defined('_JEXEC') or die;
use Joomla\CMS\MVC\Model\AdminModel;

class ProjectsModelCatalog extends AdminModel {

    public $tip;

    public function __construct(array $config = array())
    {
        $this->id =  JFactory::getApplication()->input->getInt('id', 0);
        parent::__construct($config);
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getFieldset(): string
    {
        if ($this->id == 0) return 'stand';
        $item = parent::getItem();
        $ctm = AdminModel::getInstance('Cattitle', 'ProjectsModel');
        $ct = $ctm->getItem($item->titleID);
        return ProjectsHelper::getProjectTypeName($ct->tip);
    }

    public function getTable($name = 'Catalog', $prefix = 'TableProjects', $options = array())
    {
        return JTable::getInstance($name, $prefix, $options);
    }

    public function delete(&$pks)
    {
        foreach ($pks as $pk) {
            $item = parent::getItem($pk);
            ProjectsHelper::addEvent(array('action' => 'delete', 'section' => 'catalog', 'itemID' => $pk, 'old_data' => $item));
        }
        return parent::delete($pks);
    }

    public function getItem($pk = null)
    {
        return parent::getItem($pk);
    }

    public function getForm($data = array(), $loadData = true)
    {
        $form = $this->loadForm(
            $this->option.'.catalog', 'catalog', array('control' => 'jform', 'load_data' => $loadData)
        );
        if (empty($form))
        {
            return false;
        }
        if ($this->id == 0)
        {
            $form->removeField('number');
            $form->removeField('square');
            $form->removeField('categoryID');
            $form->removeField('title');
        }
        return $form;
    }

    protected function loadFormData()
    {
        $data = JFactory::getApplication()->getUserState($this->option.'.edit.catalog.data', array());
        if (empty($data))
        {
            $data = $this->getItem();
        }

        return $data;
    }

    public function save($data)
    {
        if ($data['id'] != null) {
            $action = 'edit';
            $old = parent::getItem($data['id']);
            $itemID = $data['id'];
        }
        else {
            $action = 'add';
        }
        $s = parent::save($data);
        if ($action == 'add') {
            $old = null;
            $itemID = parent::getDbo()->insertid();
            $data['id'] = $itemID;
        }
        ProjectsHelper::addEvent(array('action' => $action, 'section' => 'catalog', 'itemID' => $itemID, 'params' => $data, 'old_data' => $old));
        return $s;
    }

    /**
     * Отправляет менеджеру уведомление об изменении площади стенда
     * @param array $data Массив с данными для уведомления
     * @since 1.0.9.10
     */
    public function sendNotify(array $data): void
    {
        $cm = AdminModel::getInstance('Contract', 'ProjectsModel');
        $em = AdminModel::getInstance('Exhibitor', 'ProjectsModel');
        $pm = AdminModel::getInstance('Project', 'ProjectsModel');
        $tm = AdminModel::getInstance('Todo', 'ProjectsModel');
        $contract = $cm->getItem($data['contractID']);
        $exhibitor = $em->getItem($contract->expID);
        $project = $pm->getItem($contract->prjID);
        $arr = array();
        $arr['id'] = NULL;
        $arr['is_notify'] = 1;
        $arr['contractID'] = $data['contractID'];
        $arr['managerID'] = $contract->managerID;
        $exhibitor = ProjectsHelper::getExpTitle($exhibitor->title_ru_short, $exhibitor->title_ru_full, $exhibitor->title_en);
        $project = $project->title;
        if ($contract->status == '1')
        {
            $number = $contract->number ?? JText::sprintf('COM_PROJECTS_WITHOUT_NUMBER');
            $arr['task'] = JText::sprintf('COM_PROJECT_TASK_STAND_DG_CATALOG_EDITED', $data['stand'], $number, $exhibitor, $project, $data['old_square'], $data['new_square']);
        }
        else
        {
            $arr['task'] = JText::sprintf('COM_PROJECT_TASK_STAND_SD_CATALOG_EDITED', $data['stand'], $exhibitor, $project, $data['old_square'], $data['new_square']);
        }
        $arr['state'] = 0;
        $tm->save($arr);
    }

    protected function prepareTable($table)
    {
        $nulls = array('number', 'categoryID', 'title', 'gos_number'); //Поля, которые NULL
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
            return $user->authorise('core.edit.state', $this->option . '.catalog.' . (int) $record->id);
        }
        else
        {
            return parent::canEditState($record);
        }
    }

    public function getScript()
    {
        return 'administrator/components/' . $this->option . '/models/forms/catalog.js';
    }

    private $id;
}