<?php
defined('_JEXEC') or die;
use Joomla\CMS\MVC\Model\ListModel;

class ProjectsModelTodos extends ListModel
{
    public function __construct(array $config)
    {
        if (empty($config['filter_fields']))
        {
            $config['filter_fields'] = array(
                't.dat',
                't.is_expire',
                't.dat_open',
                't.dat_close',
                'open',
                'manager',
                'project',
                'rubric',
                'exhibitor',
                'e.title_ru_short',
                't.number',
                'state',
                'dat',
                'is_expire desc, t.dat',
            );
        }
        parent::__construct($config);
    }

    protected function _getListQuery()
    {
        $db =& $this->getDbo();
        $query = $db->getQuery(true);
        $query
            ->select("*")
            ->from("`#__prj_todo_list` as `t`");
        /* Фильтр */
        $contractID = JFactory::getApplication()->input->getInt('contractID', 0);
        $search = $this->getState('filter.search');
        if (!empty($search) && $contractID == 0)
        {
            $search = $db->q("%{$search}%");
            $query->where("(`t`.`exhibitor` LIKE {$search})");
        }
        // Показываем уведомления
        $notify = JFactory::getApplication()->input->getInt('notify', 0);
        $query->where("`t`.`is_notify` = {$notify}");
        if ($notify == 0) {
            // Фильтруем по состоянию.
            $published = $this->getState('filter.state');
            if (is_numeric($published) && $published < 5) {
                $query->where('`t`.`state` = ' . (int) $published);
                if ($published == 0) $query->where("`is_expire` = 0");
            } elseif ($published === '') {
                $query->where('(`t`.`state` IN (0, 1))');
            }
            //Просроченные
            if ($published == 5) {
                $query->where("`is_expire` = 1");
            }
        }
        else
        {
            $query->where("`t`.`state` = 0");
        }

        // Фильтруем по менеджеру.
        $manager = $this->getState('filter.manager');
        if (is_numeric($manager)) {
            $query->where('`t`.`managerID` = ' . (int) $manager);
        }
        // Фильтруем по дате.
        $dat = $this->getState('filter.dat');
        if (!empty($dat) && $contractID == 0)
        {
            $dat = $db->q($dat);
            $query->where("`t`.`dat` = {$dat}");
        }
        // Фильтруем по экспоненту.
        $exhibitor = $this->getState('filter.exhibitor');
        if (is_numeric($exhibitor))
        {
            $query->where('`t`.`exhibitorID` = ' . (int) $exhibitor);
        }
        // Фильтруем по проекту.
        $project = $this->getState('filter.project');
        if (empty($project) && $notify == 0) $project = ProjectsHelper::getActiveProject();
        if (is_numeric($project))
        {
            $query->where('`t`.`projectID` = ' . (int) $project);
        }
        //Фильтруем по тематикам разделов
        $rubric = $this->getState('filter.rubric');
        if (is_numeric($rubric)) {
            if ($rubric != -1) {
                $ids = ProjectsHelper::getRubricContracts($rubric);
                if (!empty($ids)) {
                    $ids = implode(', ', $ids);
                    $query->where("t.contractID IN ({$ids})");
                } else {
                    $query->where("t.contractID = 0");
                }
            }
            else {
                $ids = ProjectsHelper::getRubricContracts();
                if (!empty($ids)) {
                    $ids = implode(', ', $ids);
                    $query->where("t.contractID NOT IN ({$ids})");
                }
            }
        }
        /* Сортировка */
        $orderCol  = $this->getState('list.ordering');
        $orderDirn = $this->getState('list.direction');
        $query->order($db->escape($orderCol . ' ' . $orderDirn));

        //Фильтруем по дате из URL
        $dat = JFactory::getApplication()->input->getString('date');
        $futures = JFactory::getApplication()->input->getBool('futures', false);
        $expire = JFactory::getApplication()->input->getBool('expire', false);
        if ($dat !== null && !$futures && !$expire)
        {
            $dat = $db->q($dat);
            $query->where("`t`.`dat` = {$dat}");
        }
        //Если не руководитель, выводим только назначенные пользователю задания
        if (!ProjectsHelper::canDo('core.general'))
        {
            $user = JFactory::getUser();
            $query->where("`t`.`managerID` = {$user->id}");
        }
        if ($contractID !== 0)
        {
            $query->where("`t`.`contractID` = {$contractID}");
        }
        if (ProjectsHelper::canDo('projects.access.todos.full'))
        {
            //Фильтруем по менеджеру из URL
            $man = JFactory::getApplication()->input->getInt('uid', 0);
            if ($man !== 0)
            {
                $query->where("`t`.`managerID` = {$man}");
            }
        }
        //Фильтруем по просраченным (из URL)
        if ($expire && $dat !== null) {
            $dat = $db->q($dat);
            $query
                ->where("t.dat < {$dat}")
                ->where("(t.dat_close >= {$dat} or t.is_expire = 1)");
        }
        //Фильтруем по будущим (из URL)
        if ($futures && $dat !== null) {
            $dat = $db->q($dat);
            $query
                ->where("t.dat > DATE_ADD({$dat}, interval 2 week)")
                ->where("t.dat_open <= {$dat}")
                ->where("t.state = 0");
        }

        return $query;
    }

    public function getItems()
    {
        $items = parent::getItems();
        $task = JFactory::getApplication()->input->getString('task', null);
        $format = JFactory::getApplication()->input->getString('format', 'html');
        $result_no_expire = array();
        $result_expire = array();
        $return = ProjectsHelper::getReturnUrl();
        foreach ($items as $item) {
            $arr = array();
            if ($task != 'exportxls' && $format != "raw") {
                $result = array();
                $arr['is_expire'] = $item->is_expire;
                $arr['id'] = $item->id;
                $url = JRoute::_("index.php?option=com_projects&amp;task=contract.edit&amp;id={$item->contractID}&amp;return={$return}");
                $c = ProjectsHelper::getContractTitle($item->contract_status, $item->number ?? 0, $item->contract_dat ?? '');
                $link = JHtml::link($url, $c);
                $arr['contract'] = $link;
                $url = JRoute::_("index.php?option=com_projects&amp;task=project.edit&amp;id={$item->projectID}&amp;return={$return}");
                $arr['project'] = (!ProjectsHelper::canDo('core.general')) ? $item->project : JHtml::link($url, $item->project);
                $url = JRoute::_("index.php?option=com_projects&amp;task=exhibitor.edit&amp;id={$item->exhibitorID}&amp;return={$return}");
                $arr['exp'] = JHtml::link($url, $item->exhibitor);
                $layout = (!$item->is_notify) ? 'edit' : 'notify';
                $url_todo = JRoute::_("index.php?option=com_projects&amp;view=todo&amp;layout={$layout}&amp;id={$item->id}&amp;return={$return}");
                $link = JHtml::link($url_todo, JDate::getInstance($item->dat)->format("d.m.Y"));
                $arr['dat'] = $link;
                $arr['dat_open'] = JDate::getInstance($item->dat_open)->format("d.m.Y");
                $arr['expID'] = $item->exhibitorID;
                $arr['dat_close'] = (!empty($item->dat_close)) ? JDate::getInstance($item->dat_close)->format("d.m.Y") : JText::sprintf('COM_PROJECTS_HEAD_TODO_DATE_NOT_CLOSE');
                $url = JRoute::_("index.php?option=com_projects&amp;view=todos&amp;contractID={$item->contractID}");
                $arr['task'] = JHtml::link((!$item->is_notify) ? $url : $url_todo, $item->task);
                $arr['result'] = ($arr['is_expire'] == '1') ? JText::sprintf('COM_PROJECTS_HEAD_TODO_STATE_EXPIRED') : $item->result;
                $arr['open'] = $item->open;
                $arr['manager'] = $item->manager;
                $arr['state'] = $item->state;
                $url = JRoute::_("index.php?option=com_projects&amp;task=todo.add&amp;contractID={$item->contractID}");
                $arr['start'] = JHtml::link($url, JText::sprintf('COM_PROJECTS_ACTION_TODO_CREATE'));
                if ($item->is_notify) {
                    $arr['close_notify'] = JHtml::link(JRoute::_("index.php?option=com_projects&amp;task=todos.publish&amp;id={$item->id}"), JText::sprintf('COM_PROJECTS_ACTION_CLOSE_AND_READ'));
                }
                $arr['state_text'] = ($arr['is_expire'] == '1') ? JText::sprintf('COM_PROJECTS_HEAD_TODO_STATE_EXPIRED') : ProjectsHelper::getTodoState($item->state);
                if ($item->is_expire == '0') $result_no_expire[] = $arr;
                if ($item->is_expire == '1') $result_expire[] = $arr;
            }
            else
            {
                $expired = $item->is_expire;
                $arr['dat_open'] = JDate::getInstance($item->dat_open)->format("d.m.Y");
                $arr['dat'] = JDate::getInstance($item->dat)->format("d.m.Y");
                $arr['contract'] = ProjectsHelper::getContractTitle($item->contract_status, $item->number ?? 0, $item->contract_dat ?? '');
                $arr['project'] = $item->project;
                $arr['exp'] = $item->exhibitor;
                $arr['project'] = $item->project;
                $arr['author'] = $item->open;
                $arr['manager'] = $item->manager;
                $arr['task'] = $item->task;
                $arr['result'] = $item->result ?? '';
                $arr['state_text'] = ($expired) ? JText::sprintf('COM_PROJECTS_HEAD_TODO_STATE_EXPIRED') : ProjectsHelper::getTodoState($item->state);
                $result[] = $arr;
            }
        }
        return ($task != 'exportxls' && $format != "raw") ? array_merge($result_expire, $result_no_expire) : $result;
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
        $sheet->setTitle(JText::sprintf('COM_PROJECTS_MENU_STAT'));
        for ($i = 1; $i < count($data) + 1; $i++) {
            for ($j = 0; $j < 10; $j++) {
                if ($i == 1) {
                    if ($j == 0) $sheet->setCellValueByColumnAndRow($j, $i, JText::sprintf('COM_PROJECTS_HEAD_TODO_DATE_OPEN'));
                    if ($j == 1) $sheet->setCellValueByColumnAndRow($j, $i, JText::sprintf('COM_PROJECTS_HEAD_TODO_DATE'));
                    if ($j == 2) $sheet->setCellValueByColumnAndRow($j, $i, JText::sprintf('COM_PROJECTS_HEAD_TODO_CONTRACT'));
                    if ($j == 3) $sheet->setCellValueByColumnAndRow($j, $i, JText::sprintf('COM_PROJECTS_HEAD_TODO_PROJECT'));
                    if ($j == 4) $sheet->setCellValueByColumnAndRow($j, $i, JText::sprintf('COM_PROJECTS_HEAD_TODO_EXP'));
                    if ($j == 5) $sheet->setCellValueByColumnAndRow($j, $i, JText::sprintf('COM_PROJECTS_HEAD_TODO_OPEN'));
                    if ($j == 6) $sheet->setCellValueByColumnAndRow($j, $i, JText::sprintf('COM_PROJECTS_HEAD_TODO_TASK'));
                    if ($j == 7) $sheet->setCellValueByColumnAndRow($j, $i, JText::sprintf('COM_PROJECTS_HEAD_TODO_MANAGER'));
                    if ($j == 8) $sheet->setCellValueByColumnAndRow($j, $i, JText::sprintf('COM_PROJECTS_HEAD_TODO_RESULT'));
                    if ($j == 9) $sheet->setCellValueByColumnAndRow($j, $i, JText::sprintf('COM_PROJECTS_HEAD_TODO_STATE'));
                }
                if ($j == 0) $sheet->setCellValueByColumnAndRow($j, $i + 1, $data[$i - 1]['dat_open']);
                if ($j == 1) $sheet->setCellValueByColumnAndRow($j, $i + 1, $data[$i - 1]['dat']);
                if ($j == 2) $sheet->setCellValueByColumnAndRow($j, $i + 1, $data[$i - 1]['contract']);
                if ($j == 3) $sheet->setCellValueByColumnAndRow($j, $i + 1, $data[$i - 1]['project']);
                if ($j == 4) $sheet->setCellValueByColumnAndRow($j, $i + 1, $data[$i - 1]['exp']);
                if ($j == 5) $sheet->setCellValueByColumnAndRow($j, $i + 1, $data[$i - 1]['author']);
                if ($j == 6) $sheet->setCellValueByColumnAndRow($j, $i + 1, $data[$i - 1]['task']);
                if ($j == 7) $sheet->setCellValueByColumnAndRow($j, $i + 1, $data[$i - 1]['manager']);
                if ($j == 8) $sheet->setCellValueByColumnAndRow($j, $i + 1, $data[$i - 1]['result'] ?? '');
                if ($j == 9) $sheet->setCellValueByColumnAndRow($j, $i + 1, $data[$i - 1]['state_text']);
            }
        }
        $sheet->getColumnDimension('A')->setWidth(10);
        $sheet->getColumnDimension('B')->setWidth(14);
        $sheet->getColumnDimension('C')->setWidth(24);
        $sheet->getColumnDimension('D')->setWidth(16);
        $sheet->getColumnDimension('E')->setWidth(35);
        $sheet->getColumnDimension('F')->setWidth(35);
        $sheet->getColumnDimension('G')->setWidth(72);
        $sheet->getColumnDimension('H')->setWidth(35);
        $sheet->getColumnDimension('I')->setWidth(47);
        $sheet->getColumnDimension('J')->setWidth(16);
        $sheet->getStyle('A1')->getFont()->setBold(true);
        $sheet->getStyle('B1')->getFont()->setBold(true);
        $sheet->getStyle('C1')->getFont()->setBold(true);
        $sheet->getStyle('D1')->getFont()->setBold(true);
        $sheet->getStyle('E1')->getFont()->setBold(true);
        $sheet->getStyle('F1')->getFont()->setBold(true);
        $sheet->getStyle('G1')->getFont()->setBold(true);
        $sheet->getStyle('H1')->getFont()->setBold(true);
        $sheet->getStyle('I1')->getFont()->setBold(true);
        $sheet->getStyle('J1')->getFont()->setBold(true);
        $user = JFactory::getUser()->name;
        $filename = JFile::makeSafe("Job by ". $user);
        $filename = sprintf("%s.xls", $filename);
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

    /* Сортировка по умолчанию */
    protected function populateState($ordering = 'is_expire desc, t.dat', $direction = 'asc')
    {
        $published = $this->getUserStateFromRequest($this->context . '.filter.state', 'filter_state', '', 'string');
        $this->setState('filter.state', $published);
        $search = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search', '', 'string');
        $this->setState('filter.search', $search);
        $exhibitor = $this->getUserStateFromRequest($this->context . '.filter.exhibitor', 'filter_exhibitor', '', 'string');
        $this->setState('filter.exhibitor', $exhibitor);
        $project = $this->getUserStateFromRequest($this->context . '.filter.project', 'filter_project', '', 'string');
        $this->setState('filter.project', $project);
        $rubric = $this->getUserStateFromRequest($this->context . '.filter.rubric', 'filter_rubric', '', 'string');
        $this->setState('filter.rubric', $rubric);
        $manager = $this->getUserStateFromRequest($this->context . '.filter.manager', 'filter_manager', '', 'string');
        $this->setState('filter.manager', $manager);
        $dat = $this->getUserStateFromRequest($this->context . '.filter.dat', 'filter_dat', '', 'string');
        $this->setState('filter.dat', $dat);
        parent::populateState($ordering, $direction);
    }

    protected function getStoreId($id = '')
    {
        $id .= ':' . $this->getState('filter.state');
        $id .= ':' . $this->getState('filter.search');
        $id .= ':' . $this->getState('filter.exhibitor');
        $id .= ':' . $this->getState('filter.project');
        $id .= ':' . $this->getState('filter.rubric');
        $id .= ':' . $this->getState('filter.manager');
        $id .= ':' . $this->getState('filter.dat');
        return parent::getStoreId($id);
    }
}