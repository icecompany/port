<?php
defined('_JEXEC') or die;
use Joomla\CMS\MVC\Model\ListModel;

class ProjectsModelManagerstat extends ListModel
{
    public function __construct(array $config)
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = array(
                //Sorting                   //Filter        //Cross
                'id',                       'search',       'manager',
                                            'dat',
                                            'dynamic',
            );
        }

        $this->task = JFactory::getApplication()->input->getString('task', 'display');
        $this->return = ProjectsHelper::getReturnUrl();

        parent::__construct($config);
    }

    protected function _getListQuery()
    {
        $db =& $this->getDbo();

        //Синхронизация данных
        $db->setQuery("call s7vi9_prj_save_manager_stat()")->execute();

        $query = $db->getQuery(true);
        $query
            ->select("s.*, u.name as manager")
            ->from("`#__prj_managers_stat` s")
            ->leftJoin("#__users u on u.id = s.managerID");

        $input = JFactory::getApplication()->input;

        /* Фильтр */
        $search = $this->getState('filter.search');
        if (!empty($search))
        {
            $search = $db->q("%{$search}%");
            $query->where("(u.name LIKE {$search}");
        }

        //Фильтруем по начальной дате
        $dat = $this->getState('filter.dat');
        if (!empty($dat)) {
            $dat = $db->q($dat);
        }
        else {
            $dat = "CURRENT_DATE";
        }

        //Фильтруем по динамике
        $dynamic = $this->getState('filter.dynamic');
        $period = array(
            "week" => "{$dat} + interval -1 week",
            "month" => "{$dat} + interval -1 month",
            "year" => "{$dat} + interval -1 year",
        );
        if (!empty($dynamic) && isset($period[$dynamic])) {
            $query->where("(s.dat = {$dat} OR s.dat = {$period[$dynamic]})");
        }

        // Фильтруем по менеджеру.
        $manager = $this->getState('filter.manager');
        if (is_numeric($manager)) {
            $manager = (int) $manager;
            $query->where("s.managerID = {$manager}");
        }

        // Фильтруем по проекту.
        $project = ProjectsHelper::getActiveProject();
        if (is_numeric($project)) {
            $project = (int) $project;
            $query->where("s.projectID = {$project}");
        }

        //Показываем только свои сделки, но если только неактивны фильтры по видам деятельности и тематической рубрике
        if (!ProjectsHelper::canDo('projects.access.contracts.full'))
        {
            $userID = JFactory::getUser()->id;
            $query->where("s.managerID = {$userID}");
        }

        /* Сортировка */
        $query->order("s.dat desc");

        //Лимит
        $this->setState('list.limit', 0);

        return $query;
    }

    public function getItems()
    {
        $items = parent::getItems();
        $result = array('items' => array(), 'total' => array(), 'managers' => array());
        $curdate = $this->state->get('filter.dat');

        foreach ($items as $item) {
            if ($item->dat == $curdate) {
                $result['items'][$item->managerID]['status_0']['today'] = (int) $item->status_0;
                $result['items'][$item->managerID]['status_1']['today'] = (int) $item->status_1;
                $result['items'][$item->managerID]['status_2']['today'] = (int) $item->status_2;
                $result['items'][$item->managerID]['status_3']['today'] = (int) $item->status_3;
                $result['items'][$item->managerID]['status_4']['today'] = (int) $item->status_4;
                $result['items'][$item->managerID]['status_7']['today'] = (int) $item->status_7;
                $result['items'][$item->managerID]['status_8']['today'] = (int) $item->status_8;
                $result['items'][$item->managerID]['status_9']['today'] = (int) $item->status_9;
                $result['items'][$item->managerID]['status_10']['today'] = (int) $item->status_10;
                $result['items'][$item->managerID]['exhibitors']['today'] = (int) $item->exhibitors;
                $result['items'][$item->managerID]['plan'] = (int) $item->plan;
                $result['items'][$item->managerID]['diff'] = (int) $result['items'][$item->managerID]['exhibitors']['today'] - $item->plan;
            }
            else {
                $result['items'][$item->managerID]['status_0'][$item->dat] = (int) $item->status_0;
                $result['items'][$item->managerID]['status_0']['dynamic'] = (int) $result['items'][$item->managerID]['status_0']['today'] - (int) $item->status_0;
                $result['items'][$item->managerID]['status_1'][$item->dat] = (int) $item->status_1;
                $result['items'][$item->managerID]['status_1']['dynamic'] = (int) $result['items'][$item->managerID]['status_1']['today'] - (int) $item->status_1;
                $result['items'][$item->managerID]['status_2'][$item->dat] = (int) $item->status_2;
                $result['items'][$item->managerID]['status_2']['dynamic'] = (int) $result['items'][$item->managerID]['status_2']['today'] - (int) $item->status_2;
                $result['items'][$item->managerID]['status_3'][$item->dat] = (int) $item->status_3;
                $result['items'][$item->managerID]['status_3']['dynamic'] = (int) $result['items'][$item->managerID]['status_3']['today'] - (int) $item->status_3;
                $result['items'][$item->managerID]['status_4'][$item->dat] = (int) $item->status_4;
                $result['items'][$item->managerID]['status_4']['dynamic'] = (int) $result['items'][$item->managerID]['status_4']['today'] - (int) $item->status_4;
                $result['items'][$item->managerID]['status_7'][$item->dat] = (int) $item->status_7;
                $result['items'][$item->managerID]['status_7']['dynamic'] = (int) $result['items'][$item->managerID]['status_7']['today'] - (int) $item->status_7;
                $result['items'][$item->managerID]['status_8'][$item->dat] = (int) $item->status_8;
                $result['items'][$item->managerID]['status_8']['dynamic'] = (int) $result['items'][$item->managerID]['status_8']['today'] - (int) $item->status_8;
                $result['items'][$item->managerID]['status_9'][$item->dat] = (int) $item->status_9;
                $result['items'][$item->managerID]['status_9']['dynamic'] = (int) $result['items'][$item->managerID]['status_9']['today'] - (int) $item->status_9;
                $result['items'][$item->managerID]['status_10'][$item->dat] = (int) $item->status_10;
                $result['items'][$item->managerID]['status_10']['dynamic'] = (int) $result['items'][$item->managerID]['status_10']['today'] - (int) $item->status_10;
                $result['items'][$item->managerID]['exhibitors'][$item->dat] = (int) $item->exhibitors;
                $result['items'][$item->managerID]['exhibitors']['dynamic'] = (int) $result['items'][$item->managerID]['exhibitors']['today'] - (int) $item->exhibitors;
            }
            if (!isset($result['managers'][$item->managerID])) $result['managers'][$item->managerID] = $item->manager;
        }

        return $result;
    }

    public function export($items)
    {
        JLoader::discover('PHPExcel', JPATH_LIBRARIES);
        JLoader::register('PHPExcel', JPATH_LIBRARIES . '/PHPExcel.php');
        $xls = new PHPExcel();
        $xls->setActiveSheetIndex(0);
        $sheet = $xls->getActiveSheet();
        $sheet->setTitle(JText::sprintf('COM_PROJECTS_MENU_CONTRACTS'));
        $titles = array_values($this->titlesColumns);
        $heads = array_keys($this->titlesColumns);
        //Заголовки
        for ($i = 0; $i < count($titles); $i++) {
            $sheet->setCellValueByColumnAndRow($i, 1, JText::sprintf($titles[$i]));
        }
        //Данные
        foreach ($items as $i => $item) {
            $j = 0;
            foreach ($heads as $head) {
                $sheet->setCellValueByColumnAndRow($j, $i+2, $item[$head]);
                $j++;
            }
        }
        //Стилизация
        $sheet->getColumnDimension('A')->setWidth(8);
        $sheet->getColumnDimension('B')->setWidth(14);
        $sheet->getColumnDimension('C')->setWidth(14);
        $sheet->getColumnDimension('D')->setWidth(16);
        $sheet->getColumnDimension('E')->setWidth(35);
        $sheet->getColumnDimension('F')->setWidth(35);
        $sheet->getColumnDimension('G')->setWidth(8);
        $sheet->getColumnDimension('H')->setWidth(30);
        $sheet->getColumnDimension('I')->setWidth(13);
        $sheet->getColumnDimension('J')->setWidth(8);
        $sheet->getColumnDimension('K')->setWidth(19);
        $sheet->getColumnDimension('L')->setWidth(19);
        $sheet->getColumnDimension('M')->setWidth(19);
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
        $sheet->getStyle('K1')->getFont()->setBold(true);
        $sheet->getStyle('L1')->getFont()->setBold(true);
        $sheet->getStyle('M1')->getFont()->setBold(true);
        return $xls;
    }

    /* Сортировка по умолчанию */
    protected function populateState($ordering = null, $direction = null)
    {
        $search = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search', '', 'string');
        $this->setState('filter.search', $search);
        $manager = $this->getUserStateFromRequest($this->context . '.filter.manager', 'filter_manager', '', 'string');
        $this->setState('filter.manager', $manager);
        $dat = $this->getUserStateFromRequest($this->context . '.filter.dat', 'filter_dat', date("Y-m-d"), 'string');
        $this->setState('filter.dat', $dat);
        $dynamic = $this->getUserStateFromRequest($this->context . '.filter.dynamic', 'filter_dynamic', 'week', 'string');
        $this->setState('filter.dynamic', $dynamic);

        parent::populateState('u.name', 'asc');
    }

    protected function getStoreId($id = '')
    {
        $id .= ':' . $this->getState('filter.search');
        $id .= ':' . $this->getState('filter.manager');
        $id .= ':' . $this->getState('filter.dat');
        $id .= ':' . $this->getState('filter.dynamic');
        return parent::getStoreId($id);
    }

    private function prepare(array $arr): array
    {
        if ($this->task != 'export') {
            //Edit link
            $id = $arr['id'];
            $text = JText::sprintf('COM_PROJECTS_ACTION_GO');
            $params = array('title' => "Contract ID: {$id}");
            $url = JRoute::_("index.php?option=com_projects&amp;task=contract.edit&amp;id={$id}&amp;return={$this->return}");
            $arr['edit'] = JHtml::link($url, $text, $params);
            //Project link
            if (ProjectsHelper::canDo('projects.access.projects')) {
                $id = $arr['projectID'];
                $text = $arr['project'];
                $params = array('title' => "Project ID: {$id}");
                $url = JRoute::_("index.php?option=com_projects&amp;task=project.edit&amp;id={$id}&amp;return={$this->return}");
                $arr['project'] = JHtml::link($url, $text, $params);
            }
            //Exhibitor link
            $id = $arr['exhibitorID'];
            $text = $arr['exhibitor'];
            $params = array('title' => "Exhibitor ID: {$id}");
            $url = JRoute::_("index.php?option=com_projects&amp;task=exhibitor.edit&amp;id={$id}&amp;return={$this->return}");
            $arr['exhibitor'] = JHtml::link($url, $text, $params);
            //Todos link
            $id = $arr['id'];
            $text = $arr['todos'];
            $params = array("style" => "font-size: 0.9em");
            $url = JRoute::_("index.php?option=com_projects&amp;view=todos&amp;contractID={$id}");
            $arr['todos'] = JHtml::link($url, $text, $params);
            //CoExp
            if ($arr['isCoExp'] == 1 && !empty($arr['parent'])) {
                $projectID = $arr['projectID'];
                $parentID = $arr['parentID'];
                $text = $arr['parent'];
                $url = JRoute::_("index.php?option=com_projects&amp;view=contracts_v2&amp;exhibitorID={$parentID}&amp;projectID={$projectID}");
                $arr['isCoExp'] = JHtml::link($url, $text, array('title' => "Parent's contract ID: {$parentID}"));
            }
            else {
                $arr['isCoExp'] = '';
            }
            //Currencies
            $arr['amount'] = ProjectsHelper::getCurrency((float) $arr['amount'], $arr['currency']);
            $arr['payments'] = ProjectsHelper::getCurrency((float) $arr['payments'], $arr['currency']);
            //Debt
            $debt = (float) $arr['debt'];
            $color = ""; //Цвет текста с долгом
            if ($debt == 0) $color = 'green';
            elseif ($debt < 0) $color = 'red';
            $text = ProjectsHelper::getCurrency((float) $arr['debt'], $arr['currency']);
            $arr['debt'] = "<span style='color: {$color}'>{$text}</span>";
            //For Accountants
            if (ProjectsHelper::canDo('projects.access.finanses.full')) {
                if ($debt > 0 && ($arr['status_code'] == '1' || $arr['status_code'] == '10')) {
                    $url = JRoute::_("index.php?option=com_projects&amp;task=score.add&amp;contractID={$arr['id']}&amp;return={$this->return}");
                    $arr['debt'] = JHtml::link($url, $text, array("style" => "color: {$color}"));
                }
            }
        }
        //Stands
        $arr['stands'] = implode(", ", $this->getStandsForContract($arr['id']));
        return $arr;
    }

    public function getDat(): string
    {
        $dat = new DateTime($this->state->get('filter.dat'));
        return $dat->format("d.m");
    }

    private $task, $return, $titlesColumns;
}
