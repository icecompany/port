<?php
defined('_JEXEC') or die;
use Joomla\CMS\MVC\Model\AdminModel;

class ProjectsModelContract extends AdminModel {
    public $tip;
    public function getTable($name = 'Contracts', $prefix = 'TableProjects', $options = array())
    {
        return JTable::getInstance($name, $prefix, $options);
    }

    public function getItem($pk = null)
    {
        $item = parent::getItem($pk);
        if ($item->id != null)
        {
            $item->files = $this->loadFiles();
            $item->file_list = $this->loadFiles(true);
            $item->stands = $this->getStands();
            $item->finanses = $this->getFinanses();
            $item->coExps = $this->getCoExps();
            $item->amount = ProjectsHelper::getContractAmount($item->id);
            $item->children = $this->loadCoExp($item->expID, $item->prjID);
            $item->rubrics = ProjectsHelper::getContractRubrics($item->id);
            $item->activecolumn = ProjectsHelper::getActivePriceColumn($item->id);
        }
        return $item;
    }

    public function getForm($data = array(), $loadData = true)
    {
        $form = $this->loadForm(
            $this->option.'.contract', 'contract', array('control' => 'jform', 'load_data' => $loadData)
        );
        if (empty($form))
        {
            return false;
        }
        $id = JFactory::getApplication()->input->get('id', 0);
        if ($id == 0)
        {
            if (!ProjectsHelper::canDo('core.general'))
            {
                $form->removeField('managerID');
            }
            if (!ProjectsHelper::canDo('projects.contract.allow') || $form->getValue('number') == null)
            {
                $form->removeField('number');
            }
            $form->setFieldAttribute('managerID', 'default', JFactory::getUser()->id);
            $form->removeField('dat');
            $form->removeField('children');
            $form->removeField('rubrics');
            $session = JFactory::getSession();
            $activeProject = $session->get('active_project');
            if (is_numeric($activeProject)) {
                $form->setFieldAttribute('prjID', 'default', $activeProject);
            }
        }
        if ($id != 0)
        {
            $dir = JPATH_ROOT."/images/contracts/{$id}";
            $form->setFieldAttribute('files', 'directory', (JFolder::exists($dir)) ? $dir : 'images/contracts');
        }
        return $form;
    }

    /**
     * Возвращает код типа сделки
     * @return int идентификатор типа сделки. 0 - для стендов, 1 - для делегаций
     * @since 1.1.3.6
     */
    public function getTip(): int
    {
        $item = parent::getItem();
        if ($item->id != null) {
           return ProjectsHelper::getContractType($item->id) ?? -1;
        }
        else {
            return -1;
        }
    }

    public function delete(&$pks)
    {
        $ok = true;
        $children = array(); //Сделки, у которых удялемая сделка является соэкспонентом
        foreach ($pks as $pk) {
            $item = parent::getItem($pk);
            $userID = JFactory::getUser()->id;
            if (!ProjectsHelper::canDo('projects.access.contracts.full') && $item->managerID != $userID) {
                JFactory::getApplication()->enqueueMessage(JText::sprintf('COM_PROJECTS_ERROR_CONTRACT_REMOVE_IS_NOT_YOUR_CONTRACT'), 'error');
                $ok = false;
                break;
            }
            $contracts = ProjectsHelper::getContractCoExp($item->expID, $item->prjID);
            ProjectsHelper::addEvent(array('action' => 'delete', 'section' => 'contract', 'itemID' => $pk, 'old_data' => $item));
            if (!empty($contracts)) $children = array_merge($children, $contracts);
        }
        if ($ok) {
                $ids = array_merge($pks, $children);
                foreach ($children as $child) {
                    $item = parent::getItem($child);
                    ProjectsHelper::addEvent(array('action' => 'delete', 'section' => 'contract', 'itemID' => $child, 'old_data' => $item));
                }
        }
        return (!$ok) ? false : parent::delete($ids);
    }

    /**
     * Изменяет значение колонок во всех пунктах прайса в текущем договоре
     * @param int $id ID сделки
     * @param int $columnID ID колонки
     * @since 1.2.2.9
     */
    public function setColumn(int $id = 0, int $columnID = 1): void
    {
        //Получем ID стендов
        if ($id == null) return;
        $db =& $this->getDbo();
        $query = $db->getQuery(true);
        $query
            ->select("`id`")
            ->from("`#__prj_stands`")
            ->where("`contractID` = {$id}");
        $stands = $db->setQuery($query)->loadColumn();

        if (!empty($stands)) {
            $ids = implode(", ", $stands);

            //Обновляем колонку у стендов
            $query = $db->getQuery(true);
            $query
                ->update("`#__prj_stands`")
                ->set("`columnID` = {$columnID}")
                ->where("`id` IN ({$ids})");
            $db->setQuery($query)->execute();

            //Обновляем колонку у пунктов прайса стендов
            $query = $db->getQuery(true);
            $query
                ->update("`#__prj_stands_advanced`")
                ->set("`columnID` = {$columnID}")
                ->where("`standID` IN ({$ids})");
            $db->setQuery($query)->execute();
        }

        //Обновляем пункты прайса, которые не касаются стендов
        $query = $db->getQuery(true);
        $query
            ->select("`id`")
            ->from("`#__prc_item_types`")
            ->where("`contractID` = {$id}")
            ->where("`tip`='standard'");
        $items = $db->setQuery($query)->loadColumn();
        if (empty($items)) return;
        $ids = implode(", ", $items);

        $query = $db->getQuery(true);
        $query
            ->update("`#__prj_contract_items`")
            ->set("`columnID` = {$columnID}")
            ->where("`id` IN ({$ids})");
        $db->setQuery($query)->execute();
        return;
    }

    /**
     * Сохраняет привязки рубрик к сделки
     * @param int $contractID ID сделки
     * @param array $rubrics массив с ID рубрик
     * @since 1.1.2.9
     */
    public function saveRubrics(int $contractID, array $rubrics = array()): void
    {
        $rm = AdminModel::getInstance('Ctrrubric', 'ProjectsModel');
        $already = ProjectsHelper::getContractRubrics($contractID);
        if (!empty($rubrics)) {
            foreach ($rubrics as $rubric) {
                $item = $rm->getItem(array('contractID' => $contractID, 'rubricID' => $rubric));
                $arr = array();
                $arr['id'] = $item->id;
                $arr['contractID'] = $contractID;
                $arr['rubricID'] = $rubric;
                if (in_array($rubric, $already)) {
                    if (($key = array_search($rubric, $already)) !== false) unset($already[$key]);
                }
                $rm->save($arr);
            }
        }
        foreach ($already as $rubric) {
            $item = $rm->getItem(array('contractID' => $contractID, 'rubricID' => $rubric));
            if ($item->id != null) $rm->delete($item->id);
        }
    }

    /**
     * Возвращает список дочерних экспонентов
     * @return array
     * @since 1.1.2.1
     */
    public function getCoExps(): array
    {
        $item = parent::getItem();
        if ($item->id == null) return array();
        $cid = $item->id;
        $result = array();
        $coExp = ProjectsHelper::getContractCoExp($item->expID, $item->prjID);
        if (!empty($coExp)) {
            $em = AdminModel::getInstance('Exhibitor', 'ProjectsModel');
            $return = base64_encode("index.php?option=com_projects&view=contract&layout=edit&id={$item->id}");
            foreach ($coExp as $contractID) {
                $arr = array();
                $contract = parent::getItem($contractID);
                $exhibitor = $em->getItem($contract->expID);
                $title = ProjectsHelper::getExpTitle($exhibitor->title_ru_short, $exhibitor->title_ru_full, $exhibitor->title_en);
                $url = JRoute::_("index.php?option=com_projects&amp;task=exhibitor.edit&amp;id={$exhibitor->id}&amp;return={$return}");
                $arr['exhibitor'] = JHtml::link($url, $title);
                $url = JRoute::_("index.php?option=com_projects&amp;task=contract.edit&amp;id={$contractID}&amp;return={$return}");
                $arr['contract'] = JHtml::link($url, ProjectsHelper::getContractTitle($contract->status, $contract->number ?? 0, $contract->dat ?? ''));
                $stands = ProjectsHelper::getContractStands($contractID);
                if (!empty($stands)) {
                    $sts = array();
                    foreach ($stands as $stand) {
                        $url = JRoute::_("index.php?option=com_projects&amp;task=stand.edit&amp;id={$stand->id}&amp;contractID={$stand->contractID}&amp;return={$return}");
                        $status = ProjectsHelper::getStandStatus($stand->status);
                        $name = sprintf("%s - %s", $stand->number, $status);
                        $sts[] = ($contractID != $stand->contractID) ? $name : JHtml::link($url, $name);
                    }
                    $arr['stands'] = implode(", ", $sts);
                }
                else {
                    $arr['stands'] = JText::sprintf('COM_PROJECTS_HEAD_CONTRACT_STAND_IS_EMPTY');
                }
                $result[] = $arr;
            }
        }
        return $result;
    }

    /**
     * Возвращает финансы для сделки
     * @return array
     * @since 1.0.3.6
     */
    public function getFinanses(): array
    {
        $item = parent::getItem();
        if ($item->id == null) return array();
        $result['scores'] = ProjectsHelper::getContractScores($item->id);
        $result['payments'] = ProjectsHelper::getContractPayments($item->id);
        return $result;
    }

    /**
     * Возвращает массив со стендами текущей сделки
     * @return array
     * @since 1.0.2.0
     */
    public function getStands(): array
    {
        $item = parent::getItem();
        if ($item->id == null) return array();
        $cid = $item->id;
        $items = ProjectsHelper::getContractStands($cid);
        $result = array();
        $return = base64_encode("index.php?option=com_projects&view=contract&layout=edit&id={$item->id}");
        $tip = ProjectsHelper::getContractType($cid);
        foreach ($items as $item) {
            $arr = array();
            $arr['id'] = $item->id;
            $num_field = ($tip == 0) ? 'number' : 'title';
            $url = JRoute::_("index.php?option=com_projects&amp;task=stand.edit&amp;contractID={$item->contractID}&amp;id={$item->id}&amp;return={$return}");
            $arr['number'] = ($item->contractID != $cid && $tip == 0) ? $item->$num_field : JHtml::link($url, $item->$num_field);
            $arr['freeze'] = $item->freeze;
            $arr['comment'] = $item->comment;
            $arr['sq'] = sprintf("%s %s", $item->sq, JText::sprintf('COM_PROJECTS_HEAD_ITEM_UNIT_SQM'));
            $arr['item'] = $item->item;
            $arr['scheme'] = ($item->scheme != null) ? JHtml::link("/images/contracts/{$cid}/{$item->scheme}", $item->scheme, array('target' => 'blank')) : '';
            $arr['tip'] = ProjectsHelper::getStandType($item->tip);
            $arr['status'] = ProjectsHelper::getStandStatus($item->status);
            $arr['action'] = JRoute::_("index.php?option=com_projects&amp;view=stand&amp;layout=edit&amp;id={$item->id}");
            if ($item->arrival != null) {
                $dat = JDate::getInstance($item->arrival);
                $arr['arrival'] = $dat->format("d.m.Y");
                $arr['category'] = $item->category;
                $arr['hotel'] = $item->hotel;
            }
            if ($item->department != null) {
                $dat = JDate::getInstance($item->department);
                $arr['department'] = $dat->format("d.m.Y");
            }
            $result[] = $arr;
        }
        return $result;
    }

    /**
     * Возвращает список заданий из планировщика для указанной сделки
     * @return array
     * @since 1.2.9.7
     */
    public function getTodos(): array
    {
        $result = array();
        $item = parent::getItem();
        if ($item->id == null) return $result;
        $todos = ProjectsHelper::getContractTodo($item->id);
        $return = base64_encode("index.php?option=com_projects&view=contract&layout=edit&id={$item->id}");
        foreach ($todos as $todo) {
            $arr = array();
            $arr['action'] = JRoute::_("index.php?option=com_projects&amp;view=todo&amp;id={$todo->id}&amp;layout=edit&amp;return={$return}");
            $arr['id'] = $todo->id;
            $arr['dat'] = $todo->dat;
            $arr['task'] = $todo->task;
            $arr['user'] = $todo->user;
            $arr['result'] = $todo->result;
            $arr['state'] = $todo->state;
            $arr['expired'] = (bool) $todo->expired;
            $result[] = $arr;
        }
        return $result;
    }

    /**
     * Возвращает название экспонента, с которым заключена текущая сделка
     * @return string
     * @since 1.0.3.7
     */
    public function getExhibitor(): string
    {
        $item = parent::getItem();
        if ($item->id == null) return '';
        $model = AdminModel::getInstance('Exhibitor', 'ProjectsModel');
        $exhibitor = $model->getItem($item->expID);
        return ProjectsHelper::getExpTitle($exhibitor->title_ru_short, $exhibitor->title_ru_full, $exhibitor->title_en);
    }

    /**
     * Возвращает название экспонента и название проекта, по которому проводится сделка
     * Используется в Title вьюшки
     * @return string
     * @since 1.2.9.5
     */
    public function getTitle():string
    {
        $item = parent::getItem();
        $db =& $this->getDbo();
        $query = $db->getQuery(true);
        $query
            ->select("`c`.`number`, `c`.`status`, `e`.`title_ru_short` as `exponent`, `p`.`title` as `project`")
            ->from("`#__prj_contracts` as `c`")
            ->leftJoin("`#__prj_exp` as `e` ON `e`.`id` = `c`.`expID`")
            ->leftJoin("`#__prj_projects` as `p` ON `p`.`id` = `c`.`prjID`")
            ->where("`c`.`id` = {$item->id}");
        $title = $db->setQuery($query, 0, 1)->loadObject();
        return ($title->status =='1') ? JText::sprintf('COM_PROJECTS_TITLE_CONTRACT_ACCEPT', $title->number, $title->exponent, $title->project) : JText::sprintf('COM_PROJECTS_TITLE_CONTRACT', $title->exponent, $title->project);
    }

    /**
     * Возвращает поля для заполнения из прайса для текущего договора
     * @return array
     * @since 1.2.0
     */
    public function getPrice($pks = null): array
    {
        $item = $this->getItem($pks);
        $prjID = $item->prjID;
        if ($prjID == null) return array();
        $project = AdminModel::getInstance('Project', 'ProjectsModel')->getItem(array('id'=>$prjID));
        $values = $this->getPriceValues($item->id);
        $items = $this->getPriceItems($project->priceID, $project->columnID, $values, $item->currency);
        return $items;
    }

    public function publish(&$pks, $value = 1)
    {
        if ($value == 2) {
            foreach ($pks as $pk) {
                $item = $this->getItem($pk);
                if ($item->status == '0' || $item->status == '1' || $item->status == '5' || $item->status == '6') parent::publish($pk, $value);
            }
            return true;
        }
        return parent::publish($pks, $value);
    }

    public function save($data)
    {
        if ($data['id'] != null) $old = parent::getItem($data['id']);
        if ($data['id'] == null && !ProjectsHelper::canDo('core.general')) $data['managerID'] = JFactory::getUser()->id;

        if ($data['id'] != null && $data['status'] == '4') {
            $table = $this->getTable('Stands', 'TableProjects');
            $table->load(array('contractID' => $data['id']));
            if ($table->id == null) {
                $msg = JText::sprintf('COM_PROJECTS_ERROR_SAVE_CONTRACT_NOT_STAND');
                $type = 'error';
                JFactory::getApplication()->enqueueMessage($msg, $type);
                JFactory::getApplication()->redirect($_SERVER['HTTP_REFERER']);
                return false;
            }
        }

        if ($data['dat'] != null)
        {
            $dat = JDate::getInstance($data['dat']);
            $data['dat'] = $dat->format("Y-m-d");
        }
        if ($data['dat'] == null && $data['status'] == '1')
        {
            $data['dat'] = date("Y-m-d");
        }
        $s1 = parent::save($data);
        if ($data['id'] != null && ($old->status == '1' || $old->status == '10') && $old->status != $data['status']) {
            $this->notifyNewStatus(array('contractID' => $data['id'], 'status_old' => $old->status, 'status_new' => $data['status']));
        }
        if ($data['id'] != null && ($data['doc_status'] == 1 or $data['doc_status'] == 2))
        {
            //Уведомление Ковалишину о скане договора или оригинале договора
            $n = array();
            $n['contractID'] = $data['id'];
            $n['managerID'] = 400;
            $n['task'] = JText::sprintf("COM_PROJECT_TASK_CONTRACT_NEW_DOC_STATUS_{$data['doc_status']}");
            ProjectsHelper::addNotify($n);
        }
        if ($data['id'] != null) {
            $column = ProjectsHelper::getActivePriceColumn($data['id']);
            if ($column != $data['activecolumn']) {
                $this->setActivePriceColumn($data['prjID'] ?? 0, $data['activecolumn'] ?? 0);
            }
        }
        $action = 'edit';
        if ($data['id'] == null) {
            $data['id'] = $this->_db->insertid();
            $itemID = $this->_db->insertid();
            $action = 'add';
            $old = NULL;
        }
        else {
            $itemID = $data['id'];
        }

        $this->saveRubrics($itemID, $data['rubrics'] ?? array());

        ProjectsHelper::addEvent(array('action' => $action, 'section' => 'contract', 'itemID' => $itemID, 'params' => $data, 'old_data' => $old));

        if (!empty($data['children'])) $this->setCoExp($data['children'], $data['expID'], $data['prjID']);
        $this->saveHistory($data['id'], $data['status']);
        $s2 = $this->savePrice();
        if (!empty($_FILES))
        {
            $file = ProjectsHelper::uploadFile('upload', 'contracts', $data['id']);
            $data['files'][] = $file;
        }
        $s3 = (!empty($data['files'])) ? $this->saveFiles($data['files']) : true;
        //Удаляем стенды сделки если статус отказ
        if ($data['status'] == 0 && $data['id'] != 0)
        {
            $stands = ProjectsHelper::getContractStands($data['id']);
            $sm = AdminModel::getInstance('Stand', 'ProjectsModel');
            foreach ($stands as $stand) {
                $sm->delete($stand->id);
            }
        }

        return $s1 && $s2 && $s3;
    }

    /**
     * Устанавливает новое значение активной ценовой колонки для проекта
     * @param int $projectID ID проекта
     * @param int $column номер колонки
     * @since 1.2.1.6
     */
    private function setActivePriceColumn(int $projectID, int $column): void
    {
        if ($projectID == 0 || $column == 0) return;
        $db =& $this->getDbo();
        $query = $db->getQuery(true);
        $projectID = $db->q($projectID);
        $column = $db->q($column);
        $query
            ->update("`#__prj_projects`")
            ->set("`columnID` = {$column}")
            ->where("`id` = {$projectID}");
        $db->setQuery($query, 0, 1)->execute();
    }

    /**
     * Отправляет уведомления об изменении статуса договора
     * @param array $params массив с параметрами уведомления
     * @since 1.2.0.1
     */
    private function notifyNewStatus(array $params): void
    {
        $group = (int) ProjectsHelper::getParam('notify_change_contract_status', 0);
        if ($group === 0) return;
        $users = ProjectsHelper::getUsersFromGroup($group);
        if (empty($users)) return;
        $status_old = JText::sprintf("COM_PROJECTS_HEAD_CONTRACT_STATUS_{$params['status_old']}");
        $status_new = JText::sprintf("COM_PROJECTS_HEAD_CONTRACT_STATUS_{$params['status_new']}");
        $db =& $this->getDbo();
        $query = $db->getQuery(true);
        $query
            ->select("IFNULL(`e`.`title_ru_short`,IFNULL(`e`.`title_ru_full`,IFNULL(`e`.`title_en`,' '))) as `exhibitor`")
            ->select("IFNULL(`p`.`title_ru`,IFNULL(`e`.`title_en`,' ')) as `project`")
            ->from("`#__prj_contracts` as `c`")
            ->leftJoin("`#__prj_exp` as `e` on `e`.`id` = `c`.`expID`")
            ->leftJoin("`#__prj_projects` as `p` on `p`.`id` = `c`.`prjID`")
            ->where("`c`.`id` = {$params['contractID']}");
        $info = $db->setQuery($query, 0, 1)->loadObject();
        $tm = AdminModel::getInstance('Todo', 'ProjectsModel');

        $group = ProjectsHelper::getNotifyGroup();
        foreach ($users as $user) {
            $arr = array();
            $arr['id'] = null;
            $arr['is_notify'] = 1;
            $arr['dat'] = JDate::getInstance()->format("Y-m-d");
            $arr['contractID'] = $params['contractID'];
            $arr['managerID'] = $user;
            $arr['result'] = null;
            $arr['notify_group'] = $group;
            $arr['state'] = 0;
            $arr['task'] = JText::sprintf('COM_PROJECT_TASK_CHANGE_CONTRACT_STATUS', $info->exhibitor, $info->project, $status_old, $status_new);
            $tm->save($arr);
        }
    }

    /**
     * Прописывает экспонентам из массива $exhibitors значение родителя $parentID
     * @param array $exhibitors Массив с ID экспонентов, которым нужно прописать родителя
     * @param int $parentID ID родителя
     * @param int $projectID ID проекта, в котором будут соэкспоненты
     * @since 1.0.4.5
     */
    private function setCoExp(array $exhibitors, int $parentID, int $projectID): void
    {
        $this->removeCoExp($parentID, $projectID);
        if (empty($exhibitors) || $parentID == 0) return;
        $ids = implode(", ", $exhibitors);
        if (empty($ids)) return;
        $db =& $this->getDbo();
        $query = $db->getQuery(true);
        $parentID = $db->q($parentID);
        $query
            ->update("`#__prj_contracts`")
            ->set("`parentID` = {$parentID}, `isCoExp` = 1")
            ->where("`expID` IN ({$ids})")
            ->where("`prjID` = {$projectID}");
        $db->setQuery($query)->execute();
    }

    /**
     * Возвращает массив с потомками - экспонентами, у которых текущий родитель в этом проекте
     * @param int $expID ID экспонента родителя
     * @param int $prjID ID проекта
     * @return array массив с ID дочерних экспонентов
     * @since 1.0.4.5
     */
    private function loadCoExp(int $expID, int $prjID): array
    {
        if ($expID == 0 || $prjID == 0) return array();
        $db =& $this->getDbo();
        $query = $db->getQuery(true);
        $query
            ->select("DISTINCT `expID`")
            ->from("`#__prj_contracts`")
            ->where("`parentID` = {$expID}")
            ->where("`prjID` = {$prjID}");
        return $db->setQuery($query)->loadColumn();
    }

    /**
     * Обнуляет значение потомков у экспонента-родителя
     * @param int $expID ID экспонента родителя
     * @param int $prjID ID проекта
     * @return array массив с ID дочерних экспонентов
     * @since 1.0.4.5
     */
    private function removeCoExp(int $expID, int $prjID): array
    {
        if ($expID == 0 || $prjID == 0) return array();
        $db =& $this->getDbo();
        $query = $db->getQuery(true);
        $query
            ->update("`#__prj_contracts`")
            ->set("`parentID` = NULL, `isCoExp` = 0")
            ->where("`parentID` = {$expID}")
            ->where("`prjID` = {$prjID}");
        return $db->setQuery($query)->loadColumn();
    }

    protected function loadFormData()
    {
        $data = JFactory::getApplication()->getUserState($this->option.'.edit.contract.data', array());
        if (empty($data))
        {
            $data = $this->getItem();
        }

        return $data;
    }

    protected function prepareTable($table)
    {
    	$nulls = array('status', 'dat', 'number', 'parentID', 'payerID', 'number_free'); //Поля, которые NULL
	    foreach ($nulls as $field)
	    {
		    if (!strlen($table->$field)) $table->$field = NULL;
    	}
        parent::prepareTable($table);
    }

    public function getScript()
    {
        return 'administrator/components/' . $this->option . '/models/forms/contract.js';
    }

    /**
     * Сохраняет действие пользователя в историю
     * @param int $contractID ID контракта
     * @param int $status новый статус договора
     * @since 1.2.6
     * @throws
     */
    private function saveHistory(int $contractID, int $status): void
    {
        $managerID = JFactory::getUser()->id;
        $data = array('dat' => date("Y-m-d H:i:s"), 'contractID' => $contractID, 'managerID' => $managerID, 'status' => $status);
        $model = AdminModel::getInstance('History','ProjectsModel');
        $old_status = $model->getLastStatus($contractID);
        if ($old_status == $status) return;
        $table = $model->getTable();
        $table->bind($data);
        $model->save($data);
    }

    /**
     * @param int $contractID ID договора
     * @return array
     * @since 1.2.0
     */
    private function getPriceValues(int $contractID): array
    {
        $db =& $this->getDbo();
        $query = $db->getQuery(true);
        $query
            ->select("*")
            ->from("`#__prj_stat`")
            ->where("`contractID` = {$contractID}");
        $items = $db->setQuery($query)->loadAssocList('itemID');
        return $items;
    }

    private function getPriceCounts(int $contractID): array
    {
        $db =& $this->getDbo();
        $query = $db->getQuery(true);
        $query
            ->select("*")
            ->from("`#__prj_contract_item_values`")
            ->where("`contractID` = {$contractID}");
        $items = $db->setQuery($query)->loadObjectList();
        $result = array();
        foreach ($items as $item) {
            $result[$item->itemID][$item->columnID] = $item->value;
        }
        return $result;
    }

    /**
     * Возвращает массив со стоимостями пунктов прайса в уазанной сделке (с учётом колонок)
     * @param int $contractID ID сделки
     * @return array
     * @since 1.2.1.2
     */
    private function getPriceItemsValues(int $contractID = 0): array
    {
        if ($contractID == 0) return array();
        $db =& $this->getDbo();
        $query = $db->getQuery(true);
        $query
            ->select("`itemID`, `price`, `value`")
            ->from("`#__prj_stat_items_values`")
            ->where("`contractID` = {$contractID}");
        $items = $db->setQuery($query)->loadAssocList() ?? array();
        if (empty($items)) return array();
        $result = array();
        foreach ($items as $item) {
            $result[$item['itemID']]['price'] = $item['price'];
            $result[$item['itemID']]['value'] = $item['value'];
        }
        return $result;
    }

    /**
     * Возвращает пункты указанного прайс-листа.
     * @param int $priceID ID прайс-листа.
     * @param int $columnID ID колонки прайс-листа, из которой брать цены.
     * @param array $values Значения пунктов прайса из договора.
     * @param string $currency Валюта договора.
     * @return array
     * @since 1.2.0
     */
    private function getPriceItems(int $priceID, int $columnID, array $values, string $currency): array
    {
        $item = parent::getItem();
        $contractID = $item->id;
        $db =& $this->getDbo();
        $activeColumn = ProjectsHelper::getActivePriceColumn($item->id);
        $stands = ProjectsHelper::getContractStands($item->id);
        $counts = $this->getPriceCounts($item->id);
        $amounts = $this->getPriceItemsValues($contractID);
        //exit(var_dump($counts));
        $result = array();
        $query = $db->getQuery(true);
        $query
            ->select("`i`.`id`, `i`.`unit`, `unit_2` as `isUnit2`, IFNULL(`i`.`unit_2`,'TWO_NOT_USE') as `unit_2`, `i`.`is_factor`, `i`.`is_markup`, `i`.`sectionID`")
            ->select("IFNULL(`i`.`title_ru`,`i`.`title_en`) as `title`")
            ->select("`i`.`price_{$currency}` as `price`")
            ->select("`i`.`column_1`, `i`.`column_2`, `i`.`column_3`, `i`.`application`, IFNULL(`i`.`is_sq`,0) as `is_sq`, `i`.`is_internet`, `i`.`is_electric`, `i`.`is_multimedia`, `i`.`is_water`, `i`.`is_cleaning`, `i`.`badge`, `i`.`stop`")
            ->select("`s`.`title` as `section`")
            ->from("`#__prc_items` as `i`")
            ->leftJoin("`#__prc_sections` as `s` ON `s`.`id` = `i`.`sectionID`")
            ->leftJoin("`#__prc_prices` as `p` ON `p`.`id` = `s`.`priceID`")
            ->where("`p`.`id` = {$priceID}")
            ->order("`i`.`application`, `i`.`title_ru`");
        $items = $db->setQuery($query)->loadObjectList();
        $return = base64_encode("index.php?option=com_projects&view=contract&layout=edit&id={$item->id}");
        foreach ($items as $item)
        {
            $arr = array();
            $tip = ProjectsHelper::getContractType($contractID);
            $arr['id'] = $item->id;
            $arr['section_id'] = $item->sectionID;
            $arr['title'] = $item->title;
            $pc = "column_{$activeColumn}";
            $cost = $item->price * $item->$pc;
            $arr['cost'] = sprintf("%s %s", number_format($cost, 2, ',', " "), $currency);
            $arr['currency'] = $currency;
            $arr['cost_clean'] = $cost;
            $arr['unit'] = ProjectsHelper::getUnit($item->unit);
            $arr['unit2'] = ProjectsHelper::getUnit($item->unit_2);
            $arr['isUnit2'] = $item->isUnit2;
            $arr['amount'] = $amounts[$item->id]['price'];
            $arr['value'] = $counts[$item->id];
            $arr['value2'] = $values[$item->id]['value2'];
            $arr['is_markup'] = $item->is_markup;
            $arr['markup'] = $values[$item->id]['markup'];
            $arr['is_factor'] = $item->is_factor;
            $arr['factor'] = $values[$item->id]['factor'];
            $arr['fixed'][1] = ($activeColumn != 1) ? true : false;
            $arr['fixed'][2] = ($activeColumn != 2) ? true : false;
            $arr['fixed'][3] = ($activeColumn != 3) ? true : false;
            $arr['is_sq'] = $item->is_sq;
            $arr['ctrItemID'] = $values[$item->id]['ctrItemID'];
            $arr['block'] = ($item->is_sq != 0 || $item->is_electric != 0 || $item->is_internet != 0 || $item->is_multimedia != 0 || $item->is_water != 0 || $item->is_cleaning != 0 || ($item->stop != 0 && !ProjectsHelper::canDo('core.admin'))) ? true : false;
            if ($item->is_sq || $tip != 0)
            {
                $sts = array();
                foreach ($stands as $stand) {
                    if (($stand->itemID != $item->id) || $stand->contractID != $contractID) continue;
                    if ($tip == 1) {
                        $title = sprintf("№%s (%s)", $stand->number, $stand->category);
                    }
                    else {
                        $title = $stand->number;
                    }
                    $sts[] = JHtml::link(JRoute::_("index.php?option=com_projects&amp;contractID={$stand->contractID}&amp;task=stand.edit&amp;id={$stand->id}&amp;return={$return}"), $title);
                }
                $arr['stand'] = implode(" / ", $sts);
                if (empty($arr['stand']) && $item->is_sq) $arr['value'] = 0;
                $arr['stands_count'] = count($stands);
            }
            //Автозаполнение знечения пропусков
            if ($item->badge == '1' && $arr['value'] == 0) {
                $a = (float) $amounts[21]['value'] + (float) $amounts[22]['value'] + (float) $amounts[263]['value'] + (float) $amounts[264]['value'] + (float) $amounts[24]['value'] + (float) $amounts[25]['value'];
                $b = (float) $amounts[23]['value'] + (float) $amounts[26]['value'];
                $c = round($b / 2);
                $arr['value'] = $a + $c;
                $dc = (float) $amounts[1273]['value'] + (float) $amounts[1274]['value'] + (float) $amounts[30]['value']; //Кол-во демо-центров
                $arr['value'] = round($dc * 100) + $arr['value'];
                $arr['title'] = "AUTO: ".$arr['title'];
            }
            $arr['sum'] = $amounts[$item->id]['price'];
            $arr['total'] = $amounts[$item->id]['value'];
            $arr['sum_showed'] = number_format($arr['sum'], 2, ',', ' ');
            if (!isset($result[$item->application][$item->section])) $result[$item->application][$item->section] = array();
            $result[$item->application][$item->section][$item->id] = $arr;
            unset($values[$item->id]);
        }
        return $result;
    }

    /**
     * Возвращает список файлов в указанной сделке
     * @param bool $dates Отображать ли даты загруженных файлов
     * @return array
     * @throws Exception
     * @since 1.3.0.0
     */
    private function loadFiles(bool $dates = false): array
    {
        $contractID = JFactory::getApplication()->input->getInt('id', 0);
        if ($contractID == 0) return array();
        $db =& $this->getDbo();
        $query = $db->getQuery(true);
        $query
            ->select("`path`")
            ->from("`#__prj_contract_files`")
            ->where("`contractID` = {$contractID}");
        if ($dates) {
            $query
                ->select("date_format(`dat`,'%d.%m.%Y') as `date`")
                ->order("`dat` desc");
        }
        return (!$dates) ? $db->setQuery($query)->loadColumn() : $db->setQuery($query)->loadAssocList();
    }

    /**
     * Сохраняет список файлов в сделке
     * @param array $files массив со списком файлов
     * @return bool
     * @since 1.3.0.0
     * @throws
     */
    private function saveFiles(array $files): bool
    {
        if (JFactory::getApplication()->input->getInt('id', 0) == 0) return true;
        $model = AdminModel::getInstance('Files', 'ProjectsModel');
        $contractID = JFactory::getApplication()->input->getInt('id');
        $alreadyFiles = $this->loadFiles(); //Список файлов, который на данный момент в таблице у этой сделке
        foreach ($files as $path)
        {
            if (empty($path)) continue;
            $pks = array('contractID' => $contractID, 'path' => $path);
            $row = $model->getItem($pks);
            $data = array(
                'contractID' => $contractID,
                'path' => $path,
                'id' => ($row->id != null) ? $row->id : NULL
            );
            if ($row->id == null) $data['dat'] = date("Y-m-d H:i:s");
            $s = $model->save($data);
            foreach ($alreadyFiles as $i => $file)
            {
                if ($path == $file)
                {
                    unset($alreadyFiles[$i]);
                    break;
                }
            }
            if (!$s) exit(var_dump($model->getErrors()));
        }
        foreach ($alreadyFiles as $file)
        {
            $pks = array('contractID' => $contractID, 'path' => $file);
            $row = $model->getItem($pks);
            $model->delete($row->id);
        }
        return true;
    }

    /**
     * Сохраняет значения для полей из прайс-листа для текущего договора
     * @return bool
     * @throws Exception
     * @since 1.2.0
     */
    private function savePrice(): bool
    {
        $contractID = JFactory::getApplication()->input->getInt('id');
        $post = $_POST['jform']['price'];
        if (empty($post)) return true;
        $model = AdminModel::getInstance('Ctritem', 'ProjectsModel');
        $cm = AdminModel::getInstance('Contract', 'ProjectsModel');
        $table = $model->getTable();
        $columnID = ProjectsHelper::getActivePriceColumn($contractID);
        $notifies = array();
        foreach ($post as $itemID => $values) {
            $pks = array('contractID' => $contractID, 'columnID' => $columnID, 'itemID' => $itemID);
            $row = $model->getItem($pks);
            $arr['id'] = ($row->id != null) ? $row->id : null;
            $arr['contractID'] = $contractID;
            $arr['itemID'] = $itemID;
            $arr['columnID'] = $columnID;
            foreach ($values as $field => $value) {
                if ($field == 'factor' && $value != null) $arr['factor'] = (float)(100 - $value) / 100;
                if ($field == 'markup' && $value != null) $arr['markup'] = (float)(100 + $value) / 100;
                if ($field == 'value' && $value != null) $arr['value'] = $value;
                if ($field == 'value2' && $value != null) $arr['value2'] = $value;
            }
            if (!isset($arr['markup'])) $arr['markup'] = NULL;
            if (!isset($arr['value2'])) $arr['value2'] = NULL;
            if (!isset($arr['factor'])) $arr['factor'] = NULL;
            $notifies[] = array_merge($arr, array('old_value' => $row->value ?? 0));
            if (!isset($arr['value'])) continue;
            if ($arr['value'] == 0) {
                if ($row->id != null) $model->delete($row->id);
                if ($row->id == null) continue;
            }

            $model->save($arr);
            unset($arr);
        }
        $contract = $cm->getItem($contractID);
        if (!empty($notifies) && ($contract->status == 1 || $contract->status == 10)) $this->sendNotifies($notifies);
        return true;
    }

    private function sendNotifies(array $data): void
    {
        if (empty($data)) return;
        $users = ProjectsHelper::getNotifyList();
        if (empty($users)) return;
        $db =& $this->getDbo();
        $cid = 0; //ID сделки
        $iid = array(); //Массив с ID пунктов прайса
        foreach ($data as $notify) {
            if (!isset($users[$notify['itemID']])) continue;
            if (!in_array($notify['itemID'], $iid)) $iid[] = $notify['itemID'];
            $cid = $notify['contractID'];
        }
        if (empty($cid)) return;
        $iid = implode(", ", $iid);
        $query = $db->getQuery(true);
        $query
            ->select("`id`, `title_ru`")
            ->from("`#__prc_items`")
            ->where("`id` in ({$iid})");
        $items = $db->setQuery($query)->loadAssocList('id');
        if (empty($items)) return;
        $query = $db->getQuery(true);
        $query
            ->select("`c`.`id` as `contractID`")
            ->select('IFNULL(`e`.`title_ru_short`,IFNULL(`e`.`title_ru_full`,`e`.`title_en`)) as `exhibitor`')
            ->select("`p`.`title_ru` as `project`")
            ->from("`#__prj_contracts` as `c`")
            ->leftJoin("`#__prj_exp` as `e` on `e`.`id` = `c`.`expID`")
            ->leftJoin("`#__prj_projects` as `p` on `p`.`id` = `c`.`prjID`")
            ->where("`c`.`id` = {$cid}");
        $contract = $db->setQuery($query, 0, 1)->loadAssoc();
        $tm = AdminModel::getInstance('Todo', 'ProjectsModel');
        foreach ($data as $item) {
            if (!isset($users[$item['itemID']])|| (float) $item['old_value'] == (float) $item['value']) continue;
            $group = ProjectsHelper::getNotifyGroup();
            foreach ($users[$item['itemID']] as $user) {
                $arr = array();
                $text = JText::sprintf('COM_PROJECT_TASK_VALUE_EDIT', $contract['exhibitor'], $contract['project'], $items[$item['itemID']]['title_ru'], $item['old_value'] ?? 0, (float) $item['value'] ?? 0);
                $arr['id'] = null;
                $arr['task'] = $text;
                $arr['contractID'] = $contract['contractID'];
                $arr['managerID'] = $user;
                $arr['dat'] = JDate::getInstance()->format("Y-m-d");
                $arr['is_notify'] = 1;
                $arr['state'] = 0;
                $arr['notify_group'] = $group;
                $arr['result'] = null;
                $tm->save($arr);
            }
        }
    }

}