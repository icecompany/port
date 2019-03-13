<?php
defined('_JEXEC') or die;

use Joomla\CMS\MVC\Model\ListModel;

class ProjectsModelReports extends ListModel
{
    public $type, $xls, $itemIDs;

    public function __construct(array $config)
    {
        $this->type = JFactory::getApplication()->input->getString('type', '');
        $this->itemIDs = JFactory::getApplication()->input->get('itemIDs', array(), 'array');
        $this->xls = (JFactory::getApplication()->input->getString('task') != 'exportxls') ? false : true;
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = array(
                'manager',
                'project',
                'exhibitor',
                'e.title_ru_full',
                'cnt.director_name',
                'cnt.director_post',
                'c.status',
                'c.number',
                'c.dat',
                'search',
                'fields',
                'status',
                'rubric',
                'u.name',
                'p.title',
            );
        }
        parent::__construct($config);
    }

    protected function _getListQuery()
    {
        $db =& $this->getDbo();

        $db->setQuery("SET lc_time_names = 'ru_RU'")->execute();
        $query = $db->getQuery(true);

        if ($this->type == 'exhibitors')
        {
            $project = $this->getState('filter.project');
            if (empty($project)) $project = ProjectsHelper::getActiveProject();
            $query
                ->select("IFNULL(`e`.`title_ru_full`,`e`.`title_ru_short`) as `exhibitor`, `c`.`expID` as `exhibitorID`")
                ->select("`ct`.`name` as `city`, `reg`.`name` as `region`, `ctr`.`name` as `country`")
                ->select("`cnt`.`director_name`, `cnt`.`director_post`, `cnt`.`indexcode`, `cnt`.`addr_legal_street`, `cnt`.`addr_legal_home`, `cnt`.`email`, `cnt`.`site`")
                ->select("`c`.`status`, `c`.`isCoExp`, IFNULL(`c`.`number_free`,`c`.`number`) as `number`, `c`.`dat`, `c`.`id` as `contractID`, `c`.`currency`")
                ->select("`u`.`name` as `manager`")
                ->select("`p`.`title` as `project`")
                ->select("`a`.`price`")
                ->from("`#__prj_contracts` as `c`")
                ->leftJoin("`#__prj_exp` as `e` ON `e`.`id` = `c`.`expID`")
                ->leftJoin("`#__prj_exp_contacts` as `cnt` ON `cnt`.`exbID` = `c`.`expID`")
                ->leftJoin("`#__grph_cities` as `ct` ON `ct`.`id` = `e`.`regID`")
                ->leftJoin('`#__grph_regions` as `reg` ON `reg`.`id` = `ct`.`region_id`')
                ->leftJoin('`#__grph_countries` as `ctr` ON `ctr`.`id` = `reg`.`country_id`')
                ->leftJoin('`#__users` as `u` ON `u`.`id` = `c`.`managerID`')
                ->leftJoin('`#__prj_contract_amounts` as `a` ON `a`.`contractID` = `c`.`id`')
                ->leftJoin('`#__prj_projects` as `p` ON `p`.`id` = `c`.`prjID`')
                ->where('`c`.`prjID` = ' . (int) $project);

            /* Фильтр */
            $search = $this->getState('filter.search');
            if (!empty($search)) {
                $search = $db->quote('%' . $db->escape($search, true) . '%', false);
                $query->where('(`e`.`title_ru_short` LIKE ' . $search . ' OR `e`.`title_ru_full` LIKE ' . $search . ' OR `e`.`title_en` LIKE ' . $search . ')');
            }

            //Фильтруем по тематикам разделов
            $rubric = $this->getState('filter.rubric');
            if (is_numeric($rubric)) {
                if ($rubric != -1) {
                    $ids = ProjectsHelper::getRubricContracts($rubric);
                    if (!empty($ids)) {
                        $ids = implode(', ', $ids);
                        $query->where("`c`.`id` IN ({$ids})");
                    } else {
                        $query->where("`c`.`id` = 0");
                    }
                }
                else {
                    $ids = ProjectsHelper::getRubricContracts();
                    if (!empty($ids)) {
                        $ids = implode(', ', $ids);
                        $query->where("`c`.`id` NOT IN ({$ids})");
                    }
                }
            }

            // Фильтруем по статусу.
            $status = $this->getState('filter.status');
            if (is_array($status)) {
                if (!empty($status)) {
                    if (!in_array('5', $status) && !in_array('0', $status))
                    {
                        $statuses = implode(', ', $status);
                        $query->where("`c`.`status` IN ({$statuses})");
                    }
                    if (in_array('0', $status)) {
                        $query->where("`c`.`isCoExp` = 0 AND `c`.`status` = 0");
                    }
                    if (in_array('5', $status)) {
                        $query->where("`c`.`isCoExp` = 1");
                    }
                }
                else
                {
                    $query->where("`c`.`status` IS NOT NULL");
                }
            }

            /* Сортировка */
            $orderCol = $this->state->get('list.ordering', 'e.title_ru_full');
            $orderDirn = $this->state->get('list.direction', 'asc');
            $query->order($db->escape($orderCol . ' ' . $orderDirn));
        }

        if ($this->type == 'managers') {
            $query
                ->select("*")
                ->from("`#__prj_rep_statuses`");

            // Фильтруем по менеджеру.
            $manager = $this->getState('filter.manager');
            if (is_numeric($manager)) {
                $query->where('`managerID` = ' . (int) $manager);
            }

            // Фильтруем по проекту.
            $project = $this->getState('filter.project');
            if (empty($project)) $project = ProjectsHelper::getActiveProject();
            if (is_numeric($project)) {
                $query->where('`projectID` = ' . (int)$project);
            }

            /* Сортировка */
            $orderCol = $this->state->get('list.ordering', 'manager');
            $orderDirn = $this->state->get('list.direction', 'asc');
            $query->order($db->escape($orderCol . ' ' . $orderDirn));
        }

        if ($this->type == 'todos_by_dates') {
            $query
                ->select("*")
                ->from("`#__prj_rep_todos_by_dates`");

            // Фильтруем по менеджеру.
            $manager = $this->getState('filter.manager');
            if (is_numeric($manager)) {
                $query->where('`managerID` = ' . (int) $manager);
            }

            // Фильтруем по проекту.
            $project = $this->getState('filter.project');
            if (empty($project)) $project = ProjectsHelper::getActiveProject();
            if (is_numeric($project)) {
                $query->where('`projectID` = ' . (int) $project);
            }

            /* Сортировка */
            $orderCol = $this->state->get('list.ordering', 'manager');
            $orderDirn = $this->state->get('list.direction', 'asc');
            $query->order($db->escape($orderCol . ' ' . $orderDirn));
        }

        if ($this->type == 'squares') {
            $query
                ->select("`c`.`id` as `contractID`, `c`.`number`, DATE_FORMAT(`c`.`dat`, '%d.%m.%Y') as `dat`, `c`.`currency`")
                ->select("IFNULL(`e`.`title_ru_full`,`e`.`title_ru_short`) as `exhibitor`")
                ->select("`i`.`title_ru` as `item`, `i`.`id` as `itemID`, `i`.`unit`")
                ->select("`v`.*")
                ->select("`a`.`price` as `amount`")
                ->from("`#__prj_contract_item_values` as `v`")
                ->leftJoin("`#__prj_contract_amounts` as `a` on `a`.`contractID` = `v`.`contractID`")
                ->leftJoin("`#__prc_items` as `i` on `i`.`id` = `v`.`itemID`")
                ->leftJoin("`#__prj_contracts` as `c` on `c`.`id` = `v`.`contractID`")
                ->leftJoin("`#__prj_exp` as `e` on `e`.`id` = `c`.`expID`")
                ->where("`c`.`number` is not null")
                ->where("`i`.`in_stat` = 1");

            // Фильтруем по проекту.
            $project = $this->getState('filter.project');
            if (empty($project)) $project = ProjectsHelper::getActiveProject();
            if (is_numeric($project)) {
                $query->where('`c`.`prjID` = ' . (int) $project);
            }

            /* Сортировка */
            $orderCol = $this->state->get('list.ordering', 'c.number');
            $orderDirn = $this->state->get('list.direction', 'asc');
            $query->order($db->escape($orderCol . ' ' . $orderDirn));

        }

        return $query;
    }

    public function getItems()
    {
        $items = parent::getItems();
        $result = array();
        foreach ($items as $item) {
            if ($this->type == 'exhibitors') {
                $arr = array();
                $arr['exhibitor'] = $item->exhibitor;
                $fields = $this->state->get('filter.fields');
                if (is_array($fields)) {
                    if (in_array('project', $fields)) $arr['project'] = $item->project;
                    if (in_array('director_name', $fields)) $arr['director_name'] = $item->director_name;
                    if (in_array('director_post', $fields)) $arr['director_post'] = $item->director_post;
                    if (in_array('manager', $fields)) $arr['manager'] = $item->manager;
                    if (in_array('address_legal', $fields)) $arr['address_legal'] = ProjectsHelper::buildAddress(array($item->country, $item->region, $item->indexcode, $item->city, $item->addr_legal_street, $item->addr_legal_home));
                    if (in_array('contacts', $fields)) {
                        $arr['contacts'] = implode("; ", $this->getContacts($item->exhibitorID));
                        $tmp = array();
                        if (!empty(trim($item->email))) $tmp[] = $item->email;
                        if (!empty(trim($item->site))) $tmp[] = $item->site;
                        $arr['sites'] = trim($item->site);
                    }
                    if (in_array('status', $fields)) {
                        $arr['status'] = ProjectsHelper::getExpStatus($item->status, $item->isCoExp);
                        $arr['number'] = $item->number ?? '';
                        $arr['dat'] = $item->dat ?? '';
                    }
                    if (in_array('stands', $fields)) $arr['stands'] = implode("; ", $this->getStands($item->contractID, true));
                    if (in_array('amount', $fields)) {
                        $arr['amount'] = (!$this->xls) ? ProjectsHelper::getCurrency((float) $item->price, $item->currency) : $item->price;
                    }
                    if (in_array('acts', $fields)) $arr['acts'] = implode(", ", ProjectsHelper::getExhibitorActs($item->exhibitorID));
                    if (in_array('rubrics', $fields)) $arr['rubrics'] = implode(", ", ProjectsHelper::getContractRubrics($item->contractID, true));
                }
                $result[] = $arr;
            }
            if ($this->type == 'managers') {
                if (!isset($result[$item->manager][$item->status])) $result[$item->manager][$item->status] = 0;
               $result[$item->manager][$item->status] += $item->cnt;
            }
            if ($this->type == 'todos_by_dates') {
                if (!isset($result[(!$item->is_future) ? 'current' : 'future'][$item->dat][$item->manager])) $result[(!$item->is_future) ? 'current' : 'future'][$item->dat][$item->manager] = 0;
                $result[(!$item->is_future) ? 'current' : 'future'][$item->dat][$item->manager] += $item->cnt;
            }
            if ($this->type == 'squares') {
                $arr = array();
                $arr['number'] = $item->number;
                $arr['stands'] = implode("; ", $this->getStands($item->contractID));
                $arr['dat'] = $item->dat;
                $arr['exhibitor'] = $item->exhibitor;
                $arr['amount'] = $item->amount;
                $arr['currency'] = $item->currency;
                if (!isset($result['contracts'][$item->contractID])) {
                    $result['contracts'][$item->contractID]['info'] = $arr;
                }
                $sq = array();
                $sq['item'] = $item->item;
                $sq['value'] = sprintf("%s %s", $item->value, ProjectsHelper::getUnit($item->unit));
                $result['contracts'][$item->contractID]['squares'][$item->itemID] = $sq;
                if (!isset($result['items'][$item->itemID])) $result['items'][$item->itemID] = $item->item;
            }
        }
        return $result;
    }

    /**
     * Возвращает список фильтров, которые необходимо убрать из отображения
     * @return array
     * @since 1.1.4.8
     */
    public function getNotAvailableFilters(): array {
        $result = array();
        if ($this->type == 'exhibitors') {
            $result = array('manager');
        }
        if ($this->type == 'managers') {
            $result = array('status', 'rubric', 'fields');
        }
        if ($this->type == 'todos_by_dates') {
            $result = array('status', 'rubric', 'fields');
        }
        if ($this->type == 'squares') {
            $result = array('status', 'rubric', 'fields', 'manager');
        }
        return $result;
    }

    /**
     * Возвращает массив с контактными лицами экспонента
     * @param int $exhibitorID
     * @return array
     * @since 1.1.0.6
     */
    private function getContacts(int $exhibitorID): array
    {
        $result = array();
        $contacts = ProjectsHelper::getExhibitorPersons($exhibitorID);
        foreach ($contacts as $contact) {
            $arr = array();
            if (!empty($contact->fio)) $arr[] = $contact->fio;
            if (!empty($contact->post)) $arr[] = $contact->post;
            if (!empty($contact->phone_work)) $arr[] = JText::sprintf('COM_PROJECTS_HEAD_PERSON_PHONE_WORK_SHORT', $contact->phone_work);
            if (!empty($contact->phone_mobile)) $arr[] = JText::sprintf('COM_PROJECTS_HEAD_PERSON_PHONE_MOBILE_SHORT', $contact->phone_mobile);
            if (!empty($contact->email)) $arr[] = $contact->email;
            if (!empty($contact->comment)) $arr[] = $contact->comment;
            $arr = implode(", ", $arr);
            $result[] = $arr;
        }
        return $result;
    }

    /**
     * Возвращает стенды и статусы у сделки
     * @param int $contractID
     * @param bool $status - отображать ли статус отрисовки стенда
     * @return array
     * @since 1.1.0.6
     */
    private function getStands(int $contractID, bool $status = false): array
    {
        $stands = ProjectsHelper::getContractStands($contractID);
        $result = array();
        foreach ($stands as $stand) {
            $arr = array();
            $arr['number'] = "№{$stand->number}";
            if ($status) {
                $arr['status'] = ProjectsHelper::getStandStatus($stand->status);
                $result[] = implode(" - ", $arr);
            }
            else {
                $result[] = $arr['number'];
            }
        }
        return $result;
    }

    public function exportToExcel()
    {
        if (is_array($this->state->get('filter.items'))) return;
        $items = $this->getItems();
        $data = $items;
        JLoader::discover('PHPExcel', JPATH_LIBRARIES);
        JLoader::register('PHPExcel', JPATH_LIBRARIES . '/PHPExcel.php');
        $xls = new PHPExcel();
        $xls->setActiveSheetIndex(0);
        $sheet = $xls->getActiveSheet();
        if ($this->type == 'exhibitors') {
            $indexes = array();
            $fields = $this->state->get('filter.fields');
            $sheet->setTitle(JText::sprintf('COM_PROJECTS_MENU_STAT'));
            for ($i = 1; $i < count($data) + 1; $i++) {
                for ($j = 0; $j < count($data) + 1; $j++) {
                    $index = 1;
                    if ($i == 1) {
                        if ($j == 0) $sheet->setCellValueByColumnAndRow($j, $i, JText::sprintf('COM_PROJECTS_HEAD_PAYMENT_EXP_DESC'));
                        if (is_array($fields)) {
                            if (in_array('status', $fields))
                            {
                                $indexes['status'] = $index;
                                $sheet->setCellValueByColumnAndRow($index, $i, JText::sprintf('COM_PROJECTS_HEAD_CONTRACT_STATUS_DOG'));
                                $index++;
                                $indexes['number'] = $index;
                                $sheet->setCellValueByColumnAndRow($index, $i, JText::sprintf('COM_PROJECTS_HEAD_CONTRACT_NUMBER_SHORT'));
                                $index++;
                                $indexes['dat'] = $index;
                                $sheet->setCellValueByColumnAndRow($index, $i, JText::sprintf('COM_PROJECTS_HEAD_CONTRACT_DATE'));
                                $index++;
                            }
                            if (in_array('amount', $fields))
                            {
                                $indexes['amount'] = $index;
                                $sheet->setCellValueByColumnAndRow($index, $i, JText::sprintf('COM_PROJECTS_HEAD_CONTRACT_AMOUNT_REPORT'));
                                $index++;
                            }
                            if (in_array('stands', $fields))
                            {
                                $indexes['stands'] = $index;
                                $sheet->setCellValueByColumnAndRow($index, $i, JText::sprintf('COM_PROJECTS_HEAD_CONTRACT_STAND_SHORT'));
                                $index++;
                            }
                            if (in_array('manager', $fields))
                            {
                                $indexes['manager'] = $index;
                                $sheet->setCellValueByColumnAndRow($index, $i, JText::sprintf('COM_PROJECTS_HEAD_MANAGER'));
                                $index++;
                            }
                            if (in_array('director_name', $fields))
                            {
                                $indexes['director_name'] = $index;
                                $sheet->setCellValueByColumnAndRow($index, $i, JText::sprintf('COM_PROJECTS_HEAD_EXP_CONTACT_DIRECTOR_NAME_DESC'));
                                $index++;
                            }
                            if (in_array('director_post', $fields))
                            {
                                $indexes['director_post'] = $index;
                                $sheet->setCellValueByColumnAndRow($index, $i, JText::sprintf('COM_PROJECTS_HEAD_EXP_CONTACT_DIRECTOR_POST'));
                                $index++;
                            }
                            if (in_array('address_legal', $fields))
                            {
                                $indexes['address_legal'] = $index;
                                $sheet->setCellValueByColumnAndRow($index, $i, JText::sprintf('COM_PROJECTS_HEAD_EXP_CONTACT_SPACER_LEGAL'));
                                $index++;
                            }
                            if (in_array('contacts', $fields))
                            {
                                $indexes['sites'] = $index;
                                $sheet->setCellValueByColumnAndRow($index, $i, JText::sprintf('COM_PROJECTS_HEAD_EXP_CONTACT_SITES'));
                                $index++;
                                $indexes['contacts'] = $index;
                                $sheet->setCellValueByColumnAndRow($index, $i, JText::sprintf('COM_PROJECTS_HEAD_EXP_CONTACT_NAME'));
                                $index++;
                            }
                            if (in_array('acts', $fields))
                            {
                                $indexes['acts'] = $index;
                                $sheet->setCellValueByColumnAndRow($index, $i, JText::sprintf('COM_PROJECTS_BLANK_EXHIBITOR_ACTIVITIES'));
                                $index++;
                            }
                            if (in_array('rubrics', $fields))
                            {
                                $indexes['rubrics'] = $index;
                                $sheet->setCellValueByColumnAndRow($index, $i, JText::sprintf('COM_PROJECTS_HEAD_THEMATIC_RUBRICS'));
                                $index++;
                            }
                        }
                    }
                    if ($j == 0) $sheet->setCellValueByColumnAndRow($j, $i + 1, $data[$i - 1]['exhibitor']);
                    if (is_array($fields)) {
                        if (in_array('status', $fields))
                        {
                            $sheet->setCellValueByColumnAndRow($indexes['status'], $i + 1, $data[$i - 1]['status']);
                            $sheet->setCellValueByColumnAndRow($indexes['number'], $i + 1, $data[$i - 1]['number']);
                            $sheet->setCellValueByColumnAndRow($indexes['dat'], $i + 1, $data[$i - 1]['dat']);
                        }
                        if (in_array('amount', $fields))
                        {
                            $sheet->setCellValueByColumnAndRow($indexes['amount'], $i + 1, $data[$i - 1]['amount']);
                        }
                        if (in_array('stands', $fields))
                        {
                            $sheet->setCellValueByColumnAndRow($indexes['stands'], $i + 1, $data[$i - 1]['stands']);
                        }
                        if (in_array('manager', $fields))
                        {
                            $sheet->setCellValueByColumnAndRow($indexes['manager'], $i + 1, $data[$i - 1]['manager']);
                        }
                        if (in_array('director_name', $fields))
                        {
                            $sheet->setCellValueByColumnAndRow($indexes['director_name'], $i + 1, $data[$i - 1]['director_name']);
                        }
                        if (in_array('director_post', $fields))
                        {
                            $sheet->setCellValueByColumnAndRow($indexes['director_post'], $i + 1, $data[$i - 1]['director_post']);
                        }
                        if (in_array('address_legal', $fields))
                        {
                            $sheet->setCellValueByColumnAndRow($indexes['address_legal'], $i + 1, $data[$i - 1]['address_legal']);
                        }
                        if (in_array('contacts', $fields))
                        {
                            $sheet->setCellValueByColumnAndRow($indexes['sites'], $i + 1, $data[$i - 1]['sites']);
                            $sheet->setCellValueByColumnAndRow($indexes['contacts'], $i + 1, $data[$i - 1]['contacts']);
                        }
                        if (in_array('acts', $fields))
                        {
                            $sheet->setCellValueByColumnAndRow($indexes['acts'], $i + 1, $data[$i - 1]['acts']);
                        }
                        if (in_array('rubrics', $fields))
                        {
                            $sheet->setCellValueByColumnAndRow($indexes['rubrics'], $i + 1, $data[$i - 1]['rubrics']);
                        }
                    }
                }
            }
            $filename = "Report {$this->type}";
            $filename = sprintf("%s.xls", $filename);
        }
        header("Expires: Mon, 1 Apr 1974 05:00:00 GMT");
        header("Last-Modified: " . gmdate("D,d M YH:i:s") . " GMT");
        header("Cache-Control: no-cache, must-revalidate");
        header("Pragma: public");
        header("Content-type: application/vnd.ms-excel");
        header("Content-Disposition: attachment; filename={$filename}");
        $objWriter = PHPExcel_IOFactory::createWriter($xls, 'Excel5');
        $objWriter->save('php://output');
        jexit();
    }

    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Возвращает название пункта прайс-листа
     * @return string
     * @since 1.0.5.0
     */
    public function getExhibitorTitle(): string
    {
        $db =& $this->getDbo();
        $query = $db->getQuery(true);
        $query
            ->select("`title_ru`")
            ->from("`#__prc_items`")
            ->where("`id` = {$this->type}");
        return $db->setQuery($query)->loadResult();
    }

    /* Сортировка по умолчанию */
    protected function populateState($ordering = null, $direction = null)
    {
        $search = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
        $this->setState('filter.search', $search);
        $project = $this->getUserStateFromRequest($this->context . '.filter.project', 'filter_project');
        $this->setState('filter.project', $project);
        $status = $this->getUserStateFromRequest($this->context . '.filter.status', 'filter_status');
        $this->setState('filter.status', $status);
        $fields = $this->getUserStateFromRequest($this->context . '.filter.fields', 'filter_fields');
        $this->setState('filter.fields', $fields);
        $status = $this->getUserStateFromRequest($this->context . '.filter.status', 'filter_status');
        $this->setState('filter.status', $status);
        $rubric = $this->getUserStateFromRequest($this->context . '.filter.rubric', 'filter_rubric');
        $this->setState('filter.rubric', $rubric);
        $manager = $this->getUserStateFromRequest($this->context . '.filter.manager', 'filter_manager');
        $this->setState('filter.manager', $manager);
        switch ($this->type) {
            case 'exhibitor': {
                $sort = 'e.title_ru_full';
                break;
            }
            case  'managers': {
                $sort = 'manager';
                break;
            }
            case  'todos_by_dates': {
                $sort = 'manager';
                break;
            }
            case 'squares': {
                $sort = 'c.number';
                break;
            }
            default: $sort = 'manager';
        }
        parent::populateState($sort, 'asc');
    }

    protected function getStoreId($id = '')
    {
        $id .= ':' . $this->getState('filter.search');
        $id .= ':' . $this->getState('filter.project');
        $id .= ':' . $this->getState('filter.status');
        $id .= ':' . $this->getState('filter.fields');
        $id .= ':' . $this->getState('filter.status');
        $id .= ':' . $this->getState('filter.rubric');
        $id .= ':' . $this->getState('filter.manager');
        return parent::getStoreId($id);
    }
}