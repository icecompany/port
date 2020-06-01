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
                'dat',
                'dynamic',
                'tbd.control_date',
            );
        }
        $this->intervals = array(
            "day" => "interval -1 day",
            "week" => "interval -1 week",
            "month" => "interval -1 month",
            "year" => "interval -1 year",
        );
        parent::__construct($config);
    }

    protected function _getListQuery()
    {
        $db = $this->getDbo();

        $db->setQuery("SET lc_time_names = 'ru_RU'")->execute();
        $query = $db->getQuery(true);

        if ($this->type == 'exhibitors')
        {
            $project = $this->getState('filter.project');
            if (empty($project)) $project = ProjectsHelper::getActiveProject();
            $query
                ->select("IFNULL(`e`.`title_ru_full`,ifnull(`e`.`title_ru_short`,e.title_en)) as `exhibitor`, `c`.`expID` as `exhibitorID`")
                ->select("IFNULL(`ce`.`title_ru_full`,ifnull(`ce`.`title_ru_short`,ce.title_en)) as `co_exhibitor`, `c`.`parentID` as `co_exhibitorID`")
                ->select("`ct`.`name` as `city`, `reg`.`name` as `region`, `ctr`.`name` as `country`")
                ->select("`cnt`.`director_name`, `cnt`.`director_post`, `cnt`.`indexcode`, `cnt`.`addr_legal_street`, `cnt`.`addr_legal_home`, `cnt`.`email`, `cnt`.`site`, cnt.phone_1, cnt.phone_2")
                ->select("`cnt`.`indexcode_fact`, `cnt`.`addr_fact_street`, `cnt`.`addr_fact_home`")
                ->select("`c`.`status`, `c`.`isCoExp`, IFNULL(`c`.`number_free`,`c`.`number`) as `number`, `c`.`dat`, `c`.`id` as `contractID`, `c`.`currency`")
                ->select("`u`.`name` as `manager`")
                ->select("`p`.`title` as `project`")
                ->select("c.info_catalog, c.logo_catalog, c.pvn_1, c.pvn_1a, c.pvn_1b, c.pvn_1v, c.pvn_1g, c.info_arrival, c.no_exhibit")
                ->from("`#__prj_contracts` as c")
                ->leftJoin("`#__prj_exp` as `e` ON `e`.`id` = `c`.`expID`")
                ->leftJoin("`#__prj_exp` as `ce` ON `ce`.`id` = `c`.`parentID`")
                ->leftJoin("`#__prj_exp_contacts` as `cnt` ON `cnt`.`exbID` = `c`.`expID`")
                ->leftJoin("`#__grph_cities` as `ct` ON `ct`.`id` = `e`.`regID`")
                ->leftJoin('`#__grph_regions` as `reg` ON `reg`.`id` = `ct`.`region_id`')
                ->leftJoin('`#__grph_countries` as `ctr` ON `ctr`.`id` = `reg`.`country_id`')
                ->leftJoin('`#__users` as `u` ON `u`.`id` = `c`.`managerID`')

                ->leftJoin('`#__prj_projects` as `p` ON `p`.`id` = `c`.`prjID`')
                ->where('`c`.`prjID` = ' . (int) $project);

            $fields = $this->state->get('filter.fields');
            if (is_array($fields)) {
                if (in_array('amount', $fields)) {
                    $query
                        ->select("`a`.`price`")
                        ->leftJoin('`#__prj_contract_amounts` as `a` ON `a`.`contractID` = `c`.`id`');
                }
            }

            /* Фильтр */
            $search = $this->getState('filter.search');
            if (!empty($search)) {
                $search = $db->quote('%' . $db->escape($search, true) . '%', false);
                $query->where('(`e`.`title_ru_short` LIKE ' . $search . ' OR `e`.`title_ru_full` LIKE ' . $search . ' OR `e`.`title_en` LIKE ' . $search . ')');
            }

            if (!ProjectsHelper::canDo('projects.access.contracts.full')) {
                $uid = JFactory::getUser()->id;
                $query
                    ->where("`c`.`managerID` = {$uid}");
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
                    $statuses = implode(', ', $status);
                    $query->where("`c`.`status` IN ({$statuses})");
                    if (in_array('5', $status)) {
                        $query->orWhere("`c`.`isCoExp` = 1");
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

        if ($this->type == 'tasks_by_dates') {
            $db->setQuery("call #__prj_save_manager_stat()")->execute(); //Синхронизируем данные

            $query
                ->select("tbd.dat, tbd.managerID, tbd.todos_expires as expires, tbd.todos_future as future, tbd.todos_after_next_week")
                ->select("u.name as manager")
                ->from("`#__prj_managers_stat` tbd")
                ->leftJoin("`#__users` u on u.id = tbd.managerID");

            // Фильтруем по проекту.
            $project = $this->getState('filter.project');
            if (empty($project)) $project = ProjectsHelper::getActiveProject();
            if (is_numeric($project)) {
                $query->where('`tbd`.`projectID` = ' . (int) $project);
            }

            //Фильтруем по начальной дате
            $dat = $db->q($this->getState('filter.dat'));

            //Фильтруем по динамике
            $period = $this->intervals[$this->getState('filter.dynamic')];

            if (!ProjectsHelper::canDo('projects.access.todos.full')) {
                $userID = JFactory::getUser()->id;
                $query->where("tbd.managerID = {$userID}");
            }
            $query->where("(tbd.dat = {$dat} OR tbd.dat = DATE_ADD({$dat}, {$period}))");
            $query->order("tbd.dat");
        }

        if ($this->type == 'tasks_current_week') {
            $db->setQuery("call #__prj_save_manager_stat()")->execute(); //Синхронизируем данные
            $query
                ->select("u.name as manager, s.managerID, s.dat, s.projectID, s.todos_expires, s.todos_plan, s.todos_completed, s.todos_after_next_week")
                ->from("#__prj_managers_stat s")
                ->leftJoin("`#__users` u on u.id = s.managerID");

            // Фильтруем по проекту.
            $project = $this->getState('filter.project');
            if (empty($project)) $project = ProjectsHelper::getActiveProject();
            if (is_numeric($project)) {
                $query->where('`s`.`projectID` = ' . (int) $project);
            }

            //Фильтруем по начальной дате
            $dat = $db->q($this->getState('filter.dat'));

            if (!ProjectsHelper::canDo('projects.access.todos.full')) {
                $userID = JFactory::getUser()->id;
                $query->where("s.managerID = {$userID}");
            }

            $query->where("week(s.dat, 1) = week({$dat}, 1)");
            $query->order("s.dat, u.name");
        }

        if ($this->type == 'squares') {
            $query
                ->select("`c`.`id` as `contractID`, `c`.`number`, DATE_FORMAT(`c`.`dat`, '%d.%m.%Y') as `dat`, `c`.`currency`")
                ->select("IFNULL(`e`.`title_ru_full`,`e`.`title_ru_short`) as `exhibitor`")
                ->select("`i`.`title_ru` as `item`, `i`.`id` as `itemID`, `i`.`unit`")
                ->select("`v`.*")
                ->select("`a`.`price` as `amount`")
                ->from("`#__prj_stat_items_values` as `v`")
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

        if ($this->type == 'pass') {
            $query
                ->select("`c`.`number`, `c`.`id` as `contractID`, `u`.`name` as `manager`")
                ->select('IFNULL(`e`.`title_ru_short`,IFNULL(`e`.`title_ru_full`,`e`.`title_en`)) as `exhibitor`, `e`.`title_ru_full`')
                ->select("`ec`.`site`")
                ->select("`e`.`id` as `exhibitorID`")
                ->select("`i`.`title_ru` as `item`, `i`.`unit`")
                ->select("`v`.*")
                ->from("`#__prj_stat_items_values` as `v`")
                ->leftJoin("`#__prc_items` as `i` on `i`.`id` = `v`.`itemID`")
                ->leftJoin("`#__prj_contracts` as `c` on `c`.`id` = `v`.`contractID`")
                ->leftJoin("`#__prj_exp` as `e` on `e`.`id` = `c`.`expID`")
                ->leftJoin("`#__prj_exp_contacts` as `ec` on `ec`.`exbID` = `e`.`id`")
                ->leftJoin('`#__users` as `u` ON `u`.`id` = `c`.`managerID`')
                ->where("(`i`.`in_pass` = 1 or `i`.`is_sq` = 1)");

            // Фильтруем по проекту.
            $project = $this->getState('filter.project');
            if (empty($project)) $project = ProjectsHelper::getActiveProject();
            if (is_numeric($project)) {
                $query->where('`c`.`prjID` = ' . (int) $project);
            }

            // Фильтруем по статусу.
            $status = $this->getState('filter.status');
            if (is_array($status)) {
                if (!empty($status)) {
                    $statuses = implode(', ', $status);
                    $query->where("`c`.`status` IN ({$statuses})");
                    if (in_array('5', $status)) {
                        $query->orWhere("`c`.`isCoExp` = 1");
                    }
                }
                else
                {
                    $query->where("`c`.`status` IS NOT NULL");
                    $query->where("`c`.`status` != 0");
                }
            }

            /* Сортировка */
            $orderCol = $this->state->get('list.ordering', 'c.number');
            $orderDirn = $this->state->get('list.direction', 'asc');
            $query->order($db->escape($orderCol . ' ' . $orderDirn));

        }

        $this->setState('list.limit', 0);

        return $query;
    }

    public function getItems()
    {
        $items = parent::getItems();
        $result = array();
        $itog = array();
        $in_info = array();
        foreach ($items as $item) {
            if ($this->type == 'exhibitors') {
                $arr = array();
                $arr['exhibitor'] = $item->exhibitor;
                $fields = $this->state->get('filter.fields');
                if (is_array($fields)) {
                    if (in_array('project', $fields)) $arr['project'] = $item->project;
                    if (in_array('director_name', $fields)) $arr['director_name'] = $item->director_name;
                    if (in_array('director_post', $fields)) $arr['director_post'] = $item->director_post;
                    if (in_array('phone', $fields)) {
                        $phones = array();
                        if (!empty($item->phone_1)) $phones[] = $item->phone_1;
                        if (!empty($item->phone_2)) $phones[] = $item->phone_2;
                        $arr['phones'] = implode(', ', $phones);
                    }
                    if (in_array('manager', $fields)) $arr['manager'] = $item->manager;
                    if (in_array('address_legal', $fields)) $arr['address_legal'] = ProjectsHelper::buildAddress(array($item->country, $item->region, $item->indexcode, $item->city, $item->addr_legal_street, $item->addr_legal_home));
                    if (in_array('address_fact', $fields)) $arr['address_fact'] = ProjectsHelper::buildAddress(array($item->country, $item->region, $item->indexcode_fact, $item->city, $item->addr_fact_street, $item->addr_fact_home));
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
                        if (!empty($item->co_exhibitor)) $arr['co_exhibitor'] = $item->co_exhibitor;
                    }
                    if (in_array('stands', $fields)) $arr['stands'] = implode("; ", $this->getStands($item->contractID, true));
                    if (in_array('amount', $fields)) {
                        $arr['amount'] = (!$this->xls) ? ProjectsHelper::getCurrency((float) $item->price, $item->currency) : $item->price;
                    }
                    if (in_array('acts', $fields)) $arr['acts'] = implode(", ", ProjectsHelper::getExhibitorActs($item->exhibitorID));
                    if (in_array('rubrics', $fields)) $arr['rubrics'] = implode(", ", ProjectsHelper::getContractRubrics($item->contractID, true));
                    if (in_array('forms', $fields)) {
                        $arr['info_catalog'] = JText::sprintf(!empty(($item->info_catalog)) ? 'JYES' : 'JNO');
                        $arr['logo_catalog'] = JText::sprintf(!empty(($item->logo_catalog)) ? 'JYES' : 'JNO');
                        $arr['pvn_1'] = JText::sprintf(!empty(($item->pvn_1)) ? 'JYES' : 'JNO');
                        $arr['pvn_1a'] = JText::sprintf(!empty(($item->pvn_1a)) ? 'JYES' : 'JNO');
                        $arr['pvn_1b'] = JText::sprintf(!empty(($item->pvn_1b)) ? 'JYES' : 'JNO');
                        $arr['pvn_1v'] = JText::sprintf(!empty(($item->pvn_1v)) ? 'JYES' : 'JNO');
                        $arr['pvn_1g'] = JText::sprintf(!empty(($item->pvn_1g)) ? 'JYES' : 'JNO');
                        $arr['no_exhibit'] = JText::sprintf(!empty(($item->no_exhibit)) ? 'JYES' : 'JNO');
                        $arr['info_arrival'] = JText::sprintf(!empty(($item->info_arrival)) ? 'JYES' : 'JNO');
                    }
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
            if ($this->type == 'tasks_by_dates') {
                $curdate = $this->state->get('filter.dat');
                if (!isset($result['managers'][$item->managerID])) $result['managers'][$item->managerID] = $item->manager;
                if (!isset($result['items'][$item->managerID])) $result['items'][$item->managerID] = array();

                $period = ($item->dat == $curdate) ? 'current' : 'dynamic';

                $result['items'][$item->managerID][$period]['expires'] = $item->expires;
                $result['items'][$item->managerID][$period]['after_next_week'] = $item->todos_after_next_week;

                if (!isset($result['total'][$period]['expires'])) $result['total'][$period]['expires'] = 0;
                if (!isset($result['total'][$period]['after_next_week'])) $result['total'][$period]['after_next_week'] = 0;

                $result['total'][$period]['expires'] += $item->expires ?? 0;
                $result['total'][$period]['after_next_week'] += $item->todos_after_next_week ?? 0;

                if (isset($result['items'][$item->managerID]['current']['expires']) && isset($result['items'][$item->managerID]['dynamic']['expires'])) {
                    //Считаем динамику просраченных задач
                    $result['items'][$item->managerID]['dynamic']['expires'] = $result['items'][$item->managerID]['current']['expires'] - $result['items'][$item->managerID]['dynamic']['expires'];
                }
                else {
                    //Просраченные задачи на период динамики
                    $result['items'][$item->managerID]['on_period']['expires'] = $result['items'][$item->managerID]['dynamic']['expires'];
                }

                if (isset($result['items'][$item->managerID]['current']['after_next_week']) && isset($result['items'][$item->managerID]['dynamic']['after_next_week'])) {
                    //Считаем динамику будущих задач
                    $result['items'][$item->managerID]['dynamic']['after_next_week'] = $result['items'][$item->managerID]['current']['after_next_week'] - $result['items'][$item->managerID]['dynamic']['after_next_week'];
                }
                else {
                    //Будущие задачи на период динамики
                    $result['items'][$item->managerID]['on_period']['after_next_week'] = $result['items'][$item->managerID]['dynamic']['expires'];
                }
            }
            if ($this->type == 'tasks_current_week') {
                if (!isset($result['managers'][$item->managerID])) $result['managers'][$item->managerID] = $item->manager;
                if (!in_array($item->dat, $result['dates'])) $result['dates'][] = $item->dat;
                if (!isset($result['items'][$item->managerID])) $result['items'][$item->managerID] = array();
                if (!isset($result['items'][$item->managerID][$item->dat])) $result['items'][$item->managerID][$item->dat] = array();
                $result['items'][$item->managerID][$item->dat]['todos_expires'] = $item->todos_expires;
                $result['items'][$item->managerID][$item->dat]['todos_plan'] = $item->todos_plan;
                $result['items'][$item->managerID][$item->dat]['todos_completed'] = $item->todos_completed;
                if (!isset($result['total'][$item->managerID]['todos_expires'])) $result['total'][$item->managerID]['todos_expires'] = (int) 0;
                if (!isset($result['total'][$item->managerID]['todos_plan'])) $result['total'][$item->managerID]['todos_plan'] = (int) 0;
                if (!isset($result['total'][$item->managerID]['todos_completed'])) $result['total'][$item->managerID]['todos_completed'] = (int) 0;
                if (!isset($result['total'][$item->dat]['todos_expires'])) $result['total'][$item->dat]['todos_expires'] = (int) 0;
                if (!isset($result['total'][$item->dat]['todos_plan'])) $result['total'][$item->dat]['todos_plan'] = (int) 0;
                if (!isset($result['total'][$item->dat]['todos_completed'])) $result['total'][$item->v]['todos_completed'] = (int) 0;
                $result['total'][$item->managerID]['todos_expires'] += $item->todos_expires;
                $result['total'][$item->managerID]['todos_plan'] += $item->todos_plan;
                $result['total'][$item->managerID]['todos_completed'] += $item->todos_completed;
                $result['total'][$item->dat]['todos_expires'] += $item->todos_expires;
                $result['total'][$item->dat]['todos_plan'] += $item->todos_plan;
                $result['total'][$item->dat]['todos_completed'] += $item->todos_completed;
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
            if ($this->type == 'pass') {
                $arr = array();
                if (!$this->xls) {
                    $arr['number'] = $item->number;
                    $arr['stands'] = implode("; ", $this->getStands($item->contractID));
                    $arr['exhibitor'] = $item->exhibitor;
                    $arr['site'] = $item->site;
                    $arr['manager'] = $item->manager;
                    $arr['title_ru_full'] = $item->title_ru_full;
                    $arr['contacts'] = implode("; ", $this->getContacts($item->exhibitorID));
                    if (!isset($result['contracts'][$item->contractID])) {
                        $result['contracts'][$item->contractID]['info'] = $arr;
                    }
                    $sq = array();
                    $sq['item'] = $item->item;
                    $sq['value'] = sprintf("%s %s", $item->value, ProjectsHelper::getUnit($item->unit));
                    $result['contracts'][$item->contractID]['squares'][$item->itemID] = $sq;
                    if (!isset($result['items'][$item->itemID])) $result['items'][$item->itemID] = $item->item;
                    if (!isset($result['sum'][$item->itemID])) $result['sum'][$item->itemID] = 0;
                    $result['sum'][$item->itemID] += $item->value;
                }
                else {
                    $arr['number'] = $item->number;
                    $arr['stands'] = implode("; ", $this->getStands($item->contractID));
                    $arr['exhibitor'] = $item->exhibitor;
                    $arr['site'] = $item->site;
                    $arr['manager'] = $item->manager;
                    $arr['title_ru_full'] = $item->title_ru_full;
                    $arr['contacts'] = implode("; ", $this->getContacts($item->exhibitorID));
                    if (!in_array($item->number, $in_info)) {
                        $result['info'][] = $arr;
                        $in_info[] = $item->number;
                    }
                    if (!isset($result['items'][$item->itemID]) && $item->itemID != null) $result['items'][$item->itemID] = $item->item;
                    if (!isset($result['squares'][$item->number][$item->itemID]) && $item->itemID != null) $result['squares'][$item->number][$item->itemID] = $item->value ?? 0;
                }
            }
        }
        if ($this->type == 'tasks_by_dates') {
            asort($result['managers']);
            $future = $this->getFutureTasks(array_keys($result['managers'] ?? array()));
            foreach ($result['managers'] as $id => $manager) {
                $result['items'][$id]['future'] = $future['data'][$id];
                $result['items'][$id]['future']['week'] = (int) $future['data'][$id]['week'] + (int) $result['items'][$id]['current']['expires'];
                $result['items'][$id]['dynamic']['plan_on_week'] = $result['items'][$id]['future']['week'] - (int) $future['data'][$id]['dynamic'] - ($result['items'][$id]['current']['expires'] - $result['items'][$id]['dynamic']['expires']);
            }
            $result['dates'] = $future['dates'];
        }
        return $result;
    }

    /**
     * Возвращает количество задач менеджеров на следующую неделю
     *
     * @param array $managerIDs массив с ID менеджеров
     *
     * @return array
     *
     * @since version 2.0.6
     */
    private function getFutureTasks(array $managerIDs): array
    {
        if (empty($managerIDs)) return array();
        $ids = implode(", ", $managerIDs);

        $db = $this->getDbo();
        //Фильтруем по начальной дате
        $dat = $db->q($this->state->get('filter.dat'));

        //Фильтруем по динамике
        $period = $this->intervals[$this->state->get('filter.dynamic')];

        $query = $db->getQuery(true);
        $query
            ->select("tbd.control_date, tbd.managerID, tbd.dat, tbd.current as cnt")
            ->select("if(tbd.control_date = {$dat},'week','dynamic') as tip")
            ->from("`#__prj_manager_tasks_by_dates` tbd")
            ->where("`tbd`.`managerID` IN ({$ids})")
            ->where("((tbd.control_date = DATE_ADD({$dat}, {$period}) and week(tbd.dat, 1) - week(DATE_ADD({$dat}, {$period}), 1) = 1) or ((tbd.control_date = {$dat} and week(tbd.dat, 1) - week({$dat}, 1) = 1)))")
            ->group("tbd.control_date, tbd.managerID, tbd.dat");

        // Фильтруем по проекту.
        $project = $this->state->get('filter.project');
        if (empty($project)) $project = ProjectsHelper::getActiveProject();
        if (is_numeric($project)) {
            $query->where("`tbd`.`projectID` = {$project}");
        }

        $items = $db->setQuery($query)->loadAssocList();
        $result = array();
        foreach ($items as $item) {
            $result['data'][$item['managerID']][$item['dat']] = $item['cnt'];
            if (!isset($result['data'][$item['managerID']]['week'])) $result['data'][$item['managerID']]['week'] = 0;
            if (!isset($result['data'][$item['managerID']]['dynamic'])) $result['data'][$item['managerID']]['dynamic'] = 0;
            $result['data'][$item['managerID']][$item['tip']] += $item['cnt'];
            if (!in_array($item['dat'], $result['dates']) && $item['tip'] == 'week') $result['dates'][] = $item['dat'];
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
            $result = array('manager', 'dat', 'dynamic', 'project');
        }
        if ($this->type == 'managers') {
            $result = array('status', 'rubric', 'fields', 'dat', 'dynamic');
        }
        if ($this->type == 'todos_by_dates') {
            $result = array('status', 'rubric', 'fields', 'dat', 'dynamic');
        }
        if ($this->type == 'tasks_by_dates') {
            $result = array('status', 'rubric', 'fields', 'manager', 'project');
        }
        if ($this->type == 'tasks_current_week') {
            $result = array('status', 'rubric', 'fields', 'manager', 'dynamic', 'project');
        }
        if ($this->type == 'squares') {
            $result = array('status', 'rubric', 'fields', 'manager', 'dat', 'dynamic');
        }
        if ($this->type == 'pass') {
            $result = array('rubric', 'fields', 'manager', 'dat', 'dynamic');
        }
        return $result;
    }

    public function getDat()
    {
        return $this->state->get('filter.dat', JFactory::getDate()->format("d.m.Y"));
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
                                $indexes['co_exhibitor'] = $index;
                                $sheet->setCellValueByColumnAndRow($index, $i, JText::sprintf('COM_PROJECTS_HEAD_CONTRACT_COEXP_BY'));
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
                            if (in_array('address_fact', $fields))
                            {
                                $indexes['address_fact'] = $index;
                                $sheet->setCellValueByColumnAndRow($index, $i, JText::sprintf('COM_PROJECTS_HEAD_EXP_CONTACT_SPACER_FACT'));
                                $index++;
                            }
                            if (in_array('phone', $fields))
                            {
                                $indexes['phones'] = $index;
                                $sheet->setCellValueByColumnAndRow($index, $i, JText::sprintf('COM_PROJECTS_HEAD_EXP_CONTACT_PHONES'));
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
                            if (in_array('forms', $fields))
                            {
                                $indexes['info_catalog'] = $index;
                                $sheet->setCellValueByColumnAndRow($index, $i, JText::sprintf('COM_PROJECTS_HEAD_CONTRACT_INFO_CATALOG'));
                                $index++;
                                $indexes['logo_catalog'] = $index;
                                $sheet->setCellValueByColumnAndRow($index, $i, JText::sprintf('COM_PROJECTS_HEAD_CONTRACT_LOGO_CATALOG'));
                                $index++;
                                $indexes['pvn_1'] = $index;
                                $sheet->setCellValueByColumnAndRow($index, $i, JText::sprintf('COM_PROJECTS_HEAD_CONTRACT_PVN_1'));
                                $index++;
                                $indexes['pvn_1a'] = $index;
                                $sheet->setCellValueByColumnAndRow($index, $i, JText::sprintf('COM_PROJECTS_HEAD_CONTRACT_PVN_1A'));
                                $index++;
                                $indexes['pvn_1b'] = $index;
                                $sheet->setCellValueByColumnAndRow($index, $i, JText::sprintf('COM_PROJECTS_HEAD_CONTRACT_PVN_1B'));
                                $index++;
                                $indexes['pvn_1v'] = $index;
                                $sheet->setCellValueByColumnAndRow($index, $i, JText::sprintf('COM_PROJECTS_HEAD_CONTRACT_PVN_1V'));
                                $index++;
                                $indexes['pvn_1g'] = $index;
                                $sheet->setCellValueByColumnAndRow($index, $i, JText::sprintf('COM_PROJECTS_HEAD_CONTRACT_PVN_1G'));
                                $index++;
                                $indexes['no_exhibit'] = $index;
                                $sheet->setCellValueByColumnAndRow($index, $i, JText::sprintf('COM_PROJECTS_HEAD_CONTRACT_NO_EXHIBIT'));
                                $index++;
                                $indexes['info_arrival'] = $index;
                                $sheet->setCellValueByColumnAndRow($index, $i, JText::sprintf('COM_PROJECTS_HEAD_CONTRACT_INFO_ARRIVAL'));
                                $index++;
                            }
                        }
                    }
                    if ($j == 0) $sheet->setCellValueByColumnAndRow($j, $i + 1, $data[$i - 1]['exhibitor']);
                    if (is_array($fields)) {
                        if (in_array('status', $fields))
                        {
                            $sheet->setCellValueByColumnAndRow($indexes['status'], $i + 1, $data[$i - 1]['status']);
                            $sheet->setCellValueByColumnAndRow($indexes['co_exhibitor'], $i + 1, $data[$i - 1]['co_exhibitor']);
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
                        if (in_array('address_fact', $fields))
                        {
                            $sheet->setCellValueByColumnAndRow($indexes['address_fact'], $i + 1, $data[$i - 1]['address_fact']);
                        }
                        if (in_array('phone', $fields))
                        {
                            $sheet->setCellValueByColumnAndRow($indexes['phones'], $i + 1, $data[$i - 1]['phones']);
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
                        if (in_array('forms', $fields))
                        {
                            $sheet->setCellValueByColumnAndRow($indexes['info_catalog'], $i + 1, $data[$i - 1]['info_catalog']);
                            $sheet->setCellValueByColumnAndRow($indexes['logo_catalog'], $i + 1, $data[$i - 1]['logo_catalog']);
                            $sheet->setCellValueByColumnAndRow($indexes['pvn_1'], $i + 1, $data[$i - 1]['pvn_1']);
                            $sheet->setCellValueByColumnAndRow($indexes['pvn_1a'], $i + 1, $data[$i - 1]['pvn_1a']);
                            $sheet->setCellValueByColumnAndRow($indexes['pvn_1b'], $i + 1, $data[$i - 1]['pvn_1b']);
                            $sheet->setCellValueByColumnAndRow($indexes['pvn_1v'], $i + 1, $data[$i - 1]['pvn_1v']);
                            $sheet->setCellValueByColumnAndRow($indexes['pvn_1g'], $i + 1, $data[$i - 1]['pvn_1g']);
                            $sheet->setCellValueByColumnAndRow($indexes['no_exhibit'], $i + 1, $data[$i - 1]['no_exhibit']);
                            $sheet->setCellValueByColumnAndRow($indexes['info_arrival'], $i + 1, $data[$i - 1]['info_arrival']);
                        }
                    }
                }
            }
            $filename = "Report {$this->type}";
            $filename = sprintf("%s.xls", $filename);
        }
        if ($this->type == 'pass') {
            $data = $items['info'];
            $indexes = array();
            $already = array();
            $fields = array('number', 'stands', 'exhibitor', 'title_ru_full', 'manager', 'site', 'contacts', 'squares');
            $sheet->setTitle(JText::sprintf('COM_PROJECTS_REPORT_TYPE_PASS'));
            for ($i = 1; $i < count($data) + 1; $i++) {
                for ($j = 0; $j < count($data) + 1; $j++) {
                    $index = 1;
                    if ($i == 1) {
                        if ($j == 0) $sheet->setCellValueByColumnAndRow($j, $i, JText::sprintf('COM_PROJECTS_HEAD_CONTRACT_NUMBER_SHORT'));
                        if (is_array($fields)) {
                            if (in_array('stands', $fields))
                            {
                                $indexes['stands'] = $index;
                                $sheet->setCellValueByColumnAndRow($index, $i, JText::sprintf('COM_PROJECTS_HEAD_CONTRACT_STAND_SHORT'));
                                $index++;
                            }
                            if (in_array('exhibitor', $fields))
                            {
                                $indexes['exhibitor'] = $index;
                                $sheet->setCellValueByColumnAndRow($index, $i, JText::sprintf('COM_PROJECTS_FILTER_EXHIBITOR'));
                                $index++;
                            }
                            if (in_array('title_ru_full', $fields))
                            {
                                $indexes['title_ru_full'] = $index;
                                $sheet->setCellValueByColumnAndRow($index, $i, JText::sprintf('COM_PROJECTS_HEAD_EXP_TITLE_RU_FULL_DESC'));
                                $index++;
                            }
                            if (in_array('manager', $fields))
                            {
                                $indexes['manager'] = $index;
                                $sheet->setCellValueByColumnAndRow($index, $i, JText::sprintf('COM_PROJECTS_HEAD_MANAGER'));
                                $index++;
                            }
                            if (in_array('contacts', $fields))
                            {
                                $indexes['contacts'] = $index;
                                $sheet->setCellValueByColumnAndRow($index, $i, JText::sprintf('COM_PROJECTS_HEAD_EXP_CONTACT_NAME'));
                                $index++;
                            }
                            if (in_array('site', $fields))
                            {
                                $indexes['site'] = $index;
                                $sheet->setCellValueByColumnAndRow($index, $i, JText::sprintf('COM_PROJECTS_HEAD_EXP_CONTACT_SITES'));
                                $index++;
                            }
                        }
                        foreach ($items['items'] as $itemID => $item) {
                            $sheet->setCellValueByColumnAndRow($index, $i, $item);
                            if (!isset($indexes[$itemID])) {
                                $indexes[(int) $itemID] = $index;
                            }
                            $index++;
                        }
                    }
                    if (!isset($already[$data[$i - 1]['number']])) {
                        if ($j == 0) {
                            $sheet->setCellValueByColumnAndRow($j, $i + 1, $data[$i - 1]['number']);
                        }
                        if (is_array($fields)) {
                            if (in_array('stands', $fields)) {
                                $sheet->setCellValueByColumnAndRow($indexes['stands'], $i + 1, $data[$i - 1]['stands']);
                            }
                            if (in_array('exhibitor', $fields)) {
                                $sheet->setCellValueByColumnAndRow($indexes['exhibitor'], $i + 1, $data[$i - 1]['exhibitor']);
                            }
                            if (in_array('title_ru_full', $fields)) {
                                $sheet->setCellValueByColumnAndRow($indexes['title_ru_full'], $i + 1, $data[$i - 1]['title_ru_full']);
                            }
                            if (in_array('manager', $fields)) {
                                $sheet->setCellValueByColumnAndRow($indexes['manager'], $i + 1, $data[$i - 1]['manager']);
                            }
                            if (in_array('contacts', $fields)) {
                                $sheet->setCellValueByColumnAndRow($indexes['contacts'], $i + 1, $data[$i - 1]['contacts']);
                            }
                            if (in_array('site', $fields)) {
                                $sheet->setCellValueByColumnAndRow($indexes['site'], $i + 1, $data[$i - 1]['site']);
                            }
                        }
                        foreach ($items['items'] as $itemID => $item) {
                            if ($indexes[$itemID] != 0) {
                                $sheet->setCellValueByColumnAndRow($indexes[$itemID], $i + 1, $items['squares'][$data[$i - 1]['number']][$itemID]);
                            }
                        }
                        $already[$data[$i - 1]['number']] =  1;
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
        $dat = $this->getUserStateFromRequest($this->context . '.filter.dat', 'filter_dat', JDate::getInstance()->format("Y-m-d"));
        $this->setState('filter.dat', $dat);
        $dynamic = $this->getUserStateFromRequest($this->context . '.filter.dynamic', 'filter_dynamic', 'week');
        $this->setState('filter.dynamic', $dynamic);
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
            case  'tasks_by_dates': {
                $sort = 'tdb.control_date';
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
        $id .= ':' . $this->getState('filter.dat', JDate::getInstance()->format("Y-m-d"));
        $id .= ':' . $this->getState('filter.dynamic', 'week');
        return parent::getStoreId($id);
    }

    private $curdate, $period, $intervals;
}