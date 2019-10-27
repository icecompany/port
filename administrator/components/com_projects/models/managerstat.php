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
                'id', 'search', 'manager',
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
        if (!empty($search)) {
            $search = $db->q("%{$search}%");
            $query->where("(u.name LIKE {$search}");
        }

        //Фильтруем по начальной дате
        $dat = $this->getState('filter.dat');
        if (!empty($dat)) {
            $dat = $db->q($dat);
        } else {
            $dat = "CURRENT_DATE";
        }

        //Фильтруем по динамике
        $dynamic = $this->getState('filter.dynamic');
        $period = array(
            "day" => "{$dat} + interval -1 day",
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
            $manager = (int)$manager;
            $query->where("s.managerID = {$manager}");
        }

        // Фильтруем по проекту.
        $project = ProjectsHelper::getActiveProject();
        if (is_numeric($project)) {
            $project = (int)$project;
            $query->where("s.projectID = {$project}");
        }

        //Показываем только свои сделки, но если только неактивны фильтры по видам деятельности и тематической рубрике
        if (!ProjectsHelper::canDo('projects.access.contracts.full')) {
            $userID = JFactory::getUser()->id;
            $query->where("s.managerID = {$userID}");
        }

        /* Сортировка */
        $query->order("u.name, s.dat desc");

        //Лимит
        $this->setState('list.limit', 0);

        return $query;
    }

    public function getItems()
    {
        $items = parent::getItems();
        $result = array('items' => array(), 'total' => array(), 'managers' => array());
        $curdate = $this->state->get('filter.dat');

        $cwt = $this->getContractsWithoutTodosCount(); //Сделки без активных задач

        foreach ($items as $item) {
            if ($item->dat == $curdate) {
                $result['items'][$item->managerID]['status_0']['today'] = (int)$item->status_0;
                if (!isset($result['total']['status_0']['today'])) $result['total']['status_0']['today'] = 0;
                $result['total']['status_0']['today'] += (int)$item->status_0;

                $result['items'][$item->managerID]['status_1']['today'] = (int)$item->status_1;
                if (!isset($result['total']['status_1']['today'])) $result['total']['status_1']['today'] = 0;
                $result['total']['status_1']['today'] += (int)$item->status_1;

                $result['items'][$item->managerID]['status_2']['today'] = (int)$item->status_2;
                if (!isset($result['total']['status_2']['today'])) $result['total']['status_2']['today'] = 0;
                $result['total']['status_2']['today'] += (int)$item->status_2;

                $result['items'][$item->managerID]['status_3']['today'] = (int)$item->status_3;
                if (!isset($result['total']['status_3']['today'])) $result['total']['status_3']['today'] = 0;
                $result['total']['status_3']['today'] += (int)$item->status_3;

                $result['items'][$item->managerID]['status_4']['today'] = (int)$item->status_4;
                if (!isset($result['total']['status_4']['today'])) $result['total']['status_4']['today'] = 0;
                $result['total']['status_4']['today'] += (int)$item->status_4;

                $result['items'][$item->managerID]['status_7']['today'] = (int)$item->status_7;
                if (!isset($result['total']['status_7']['today'])) $result['total']['status_7']['today'] = 0;
                $result['total']['status_7']['today'] += (int)$item->status_7;

                $result['items'][$item->managerID]['status_8']['today'] = (int)$item->status_8;
                if (!isset($result['total']['status_8']['today'])) $result['total']['status_8']['today'] = 0;
                $result['total']['status_8']['today'] += (int)$item->status_8;

                $result['items'][$item->managerID]['status_9']['today'] = (int)$item->status_9;
                if (!isset($result['total']['status_9']['today'])) $result['total']['status_9']['today'] = 0;
                $result['total']['status_9']['today'] += (int)$item->status_9;

                $result['items'][$item->managerID]['status_10']['today'] = (int)$item->status_10;
                if (!isset($result['total']['status_10']['today'])) $result['total']['status_10']['today'] = 0;
                $result['total']['status_10']['today'] += (int)$item->status_10;

                $result['items'][$item->managerID]['exhibitors']['today'] = (int)$item->exhibitors;
                if (!isset($result['total']['exhibitors']['today'])) $result['total']['exhibitors']['today'] = 0;
                $result['total']['exhibitors']['today'] += (int)$item->exhibitors;

                $params = array(
                    "option" => "com_projects",
                    "view" => "contracts_v2",
                    "managerID" => $item->managerID,
                    "cwt" => "1",
                );
                $url = JRoute::_("index.php?" . http_build_query($params));
                $attribs = array("target" => "_blank");
                if (!empty($cwt[$item->managerID]['cnt'])) {
                    $result['items'][$item->managerID]['cwt'] = $cwt[$item->managerID]['cnt'];
                    if ($this->task != 'export') {
                        $result['items'][$item->managerID]['cwt'] = JHtml::link($url, $cwt[$item->managerID]['cnt'], $attribs);
                    }
                    if (!isset($result['total']['cwt'])) $result['total']['cwt'] = 0;
                    $result['total']['cwt'] += (int)$cwt[$item->managerID]['cnt'];
                } else {
                    $result['items'][$item->managerID]['cwt'] = 0;
                }
            } else {
                $result['items'][$item->managerID]['status_0'][$item->dat] = (int)$item->status_0;
                $result['items'][$item->managerID]['status_0']['dynamic'] = (int)$result['items'][$item->managerID]['status_0']['today'] - (int)$item->status_0;
                if (!isset($result['total']['status_0']['dynamic'])) $result['total']['status_0']['dynamic'] = 0;
                $result['total']['status_0']['dynamic'] += (int)$result['items'][$item->managerID]['status_0']['dynamic'];

                $result['items'][$item->managerID]['status_1'][$item->dat] = (int)$item->status_1;
                $result['items'][$item->managerID]['status_1']['dynamic'] = (int)$result['items'][$item->managerID]['status_1']['today'] - (int)$item->status_1;
                if (!isset($result['total']['status_1']['dynamic'])) $result['total']['status_1']['dynamic'] = 0;
                $result['total']['status_1']['dynamic'] += (int)$result['items'][$item->managerID]['status_1']['dynamic'];

                $result['items'][$item->managerID]['status_2'][$item->dat] = (int)$item->status_2;
                $result['items'][$item->managerID]['status_2']['dynamic'] = (int)$result['items'][$item->managerID]['status_2']['today'] - (int)$item->status_2;
                if (!isset($result['total']['status_2']['dynamic'])) $result['total']['status_2']['dynamic'] = 0;
                $result['total']['status_2']['dynamic'] += (int)$result['items'][$item->managerID]['status_2']['dynamic'];

                $result['items'][$item->managerID]['status_3'][$item->dat] = (int)$item->status_3;
                $result['items'][$item->managerID]['status_3']['dynamic'] = (int)$result['items'][$item->managerID]['status_3']['today'] - (int)$item->status_3;
                if (!isset($result['total']['status_3']['dynamic'])) $result['total']['status_3']['dynamic'] = 0;
                $result['total']['status_3']['dynamic'] += (int)$result['items'][$item->managerID]['status_3']['dynamic'];

                $result['items'][$item->managerID]['status_4'][$item->dat] = (int)$item->status_4;
                $result['items'][$item->managerID]['status_4']['dynamic'] = (int)$result['items'][$item->managerID]['status_4']['today'] - (int)$item->status_4;
                if (!isset($result['total']['status_4']['dynamic'])) $result['total']['status_4']['dynamic'] = 0;
                $result['total']['status_4']['dynamic'] += (int)$result['items'][$item->managerID]['status_4']['dynamic'];

                $result['items'][$item->managerID]['status_7'][$item->dat] = (int)$item->status_7;
                $result['items'][$item->managerID]['status_7']['dynamic'] = (int)$result['items'][$item->managerID]['status_7']['today'] - (int)$item->status_7;
                if (!isset($result['total']['status_7']['dynamic'])) $result['total']['status_7']['dynamic'] = 0;
                $result['total']['status_7']['dynamic'] += (int)$result['items'][$item->managerID]['status_7']['dynamic'];

                $result['items'][$item->managerID]['status_8'][$item->dat] = (int)$item->status_8;
                $result['items'][$item->managerID]['status_8']['dynamic'] = (int)$result['items'][$item->managerID]['status_8']['today'] - (int)$item->status_8;
                if (!isset($result['total']['status_8']['dynamic'])) $result['total']['status_8']['dynamic'] = 0;
                $result['total']['status_8']['dynamic'] += (int)$result['items'][$item->managerID]['status_8']['dynamic'];

                $result['items'][$item->managerID]['status_9'][$item->dat] = (int)$item->status_9;
                $result['items'][$item->managerID]['status_9']['dynamic'] = (int)$result['items'][$item->managerID]['status_9']['today'] - (int)$item->status_9;
                if (!isset($result['total']['status_9']['dynamic'])) $result['total']['status_9']['dynamic'] = 0;
                $result['total']['status_9']['dynamic'] += (int)$result['items'][$item->managerID]['status_9']['dynamic'];

                $result['items'][$item->managerID]['status_10'][$item->dat] = (int)$item->status_10;
                $result['items'][$item->managerID]['status_10']['dynamic'] = (int)$result['items'][$item->managerID]['status_10']['today'] - (int)$item->status_10;
                if (!isset($result['total']['status_10']['dynamic'])) $result['total']['status_10']['dynamic'] = 0;
                $result['total']['status_10']['dynamic'] += (int)$result['items'][$item->managerID]['status_10']['dynamic'];

                $result['items'][$item->managerID]['exhibitors'][$item->dat] = (int)$item->exhibitors;
                $result['items'][$item->managerID]['exhibitors']['dynamic'] = (int)$result['items'][$item->managerID]['exhibitors']['today'] - (int)$item->exhibitors;
                if (!isset($result['total']['exhibitors']['dynamic'])) $result['total']['exhibitors']['dynamic'] = 0;
                $result['total']['exhibitors']['dynamic'] += $result['items'][$item->managerID]['exhibitors']['dynamic'];
            }
            if (!isset($result['managers'][$item->managerID])) $result['managers'][$item->managerID] = $item->manager;
        }
        if ($this->task != 'export') {
            $result['total']['cwt'] = JHtml::link(JRoute::_("index.php?option=com_projects&amp;view=contracts_v2&amp;cwt=1"), $result['total']['cwt'], array('target' => '_blank'));
        }
        return $result;
    }

    public function export()
    {
        $items = $this->getItems();
        JLoader::discover('PHPExcel', JPATH_LIBRARIES);
        JLoader::register('PHPExcel', JPATH_LIBRARIES . '/PHPExcel.php');
        $projectID = ProjectsHelper::getActiveProject();
        $dat = $this->getDat(true);

        $xls = new PHPExcel();
        $xls->setActiveSheetIndex(0);
        $sheet = $xls->getActiveSheet();

        //Объединение столбцов
        $merge = array("A1:Q1", "A2:A3", "B2:B3", "C2:D2", "E2:F2", "G2:H2", "I2:J2", "K2:L2",
            "M2:N2", "O2:O3", "P2:P3", "Q2:Q3", "A12:B12");
        foreach ($merge as $value) {
            $sheet->mergeCells($value);
        }

        //Выравнивание
        $alignment = array("A1", "B2", "C2", "E2", "G2", "I2", "K2", "M2", "O2", "P2", "Q2");
        foreach ($alignment as $cell) {
            $sheet->getStyle($cell)->applyFromArray(array(
                    "alignment" => array(
                        'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER),
                    "font" => array(
                        "bold" => true,
                    ),
                )
            );
        }

        //Ширина столбцов
        $width = array("A" => 4, "B" => 36, "C" => 10, "D" => 10, "E" => 10, "F" => 10, "G" => 10, "H" => 10,
            "I" => 10, "J" => 10, "K" => 10, "L" => 10, "M" => 10, "N" => 10, "O" => 12, "P" => "12", "Q" => 12);
        foreach ($width as $col => $value) {
            $sheet->getColumnDimension($col)->setWidth($value);
        }

        //Формат данных
        $cells = array("C3", "E3", "G3", "I3", "K3", "M3");
        foreach ($cells as $cell) {
            $sheet->getStyle($cell)->applyFromArray(
                array(
                    "numberformat" => array(
                        'code' => PHPExcel_Style_NumberFormat::FORMAT_DATE_DMMINUS
                    ),
                ),
            );
        }

        $sheet->setCellValue("A1", JText::sprintf('COM_PROJECTS_REPORT_TITLE'));
        $sheet->setCellValue("A2", "№");
        $sheet->setCellValue("B2", JText::sprintf('COM_PROJECTS_HEAD_MANAGER_STAT_MANAGER'));
        $sheet->setCellValue("C2", JText::sprintf('COM_PROJECTS_HEAD_CONTRACT_STATUS_2_SHORT'));
        $sheet->setCellValue("E2", JText::sprintf('COM_PROJECTS_HEAD_CONTRACT_STATUS_3_SHORT'));
        $sheet->setCellValue("G2", JText::sprintf('COM_PROJECTS_HEAD_CONTRACT_STATUS_4_SHORT'));
        $sheet->setCellValue("I2", JText::sprintf('COM_PROJECTS_HEAD_CONTRACT_STATUS_1_SHORT'));
        $sheet->setCellValue("K2", JText::sprintf('COM_PROJECTS_HEAD_CONTRACT_STATUS_9_SHORT'));
        $sheet->setCellValue("M2", JText::sprintf('COM_PROJECTS_HEAD_CONTRACT_STATUS_0_SHORT'));
        $sheet->setCellValue("O2", JText::sprintf('COM_PROJECTS_HEAD_MANAGER_STAT_COMPANIES'));
        $sheet->setCellValue("P2", JText::sprintf('COM_PROJECTS_HEAD_MANAGER_STAT_DYNAMIC'));
        $sheet->setCellValue("Q2", JText::sprintf('COM_PROJECTS_HEAD_CONTRACTS_WITHOUT_TODOS'));

        $sheet->setCellValue("C3", $dat);
        $sheet->setCellValue("D3", JText::sprintf('COM_PROJECTS_HEAD_MANAGER_STAT_DYNAMIC'));
        $sheet->setCellValue("E3", $dat);
        $sheet->setCellValue("F3", JText::sprintf('COM_PROJECTS_HEAD_MANAGER_STAT_DYNAMIC'));
        $sheet->setCellValue("G3", $dat);
        $sheet->setCellValue("H3", JText::sprintf('COM_PROJECTS_HEAD_MANAGER_STAT_DYNAMIC'));
        $sheet->setCellValue("I3", $dat);
        $sheet->setCellValue("J3", JText::sprintf('COM_PROJECTS_HEAD_MANAGER_STAT_DYNAMIC'));
        $sheet->setCellValue("K3", $dat);
        $sheet->setCellValue("L3", JText::sprintf('COM_PROJECTS_HEAD_MANAGER_STAT_DYNAMIC'));
        $sheet->setCellValue("M3", $dat);
        $sheet->setCellValue("N3", JText::sprintf('COM_PROJECTS_HEAD_MANAGER_STAT_DYNAMIC'));
        $sheet->setCellValue("O2", JText::sprintf('COM_PROJECTS_HEAD_MANAGER_STAT_COMPANIES'));
        $sheet->setCellValue("P2", JText::sprintf('COM_PROJECTS_HEAD_MANAGER_STAT_DYNAMIC'));
        $sheet->setCellValue("Q2", JText::sprintf('COM_PROJECTS_HEAD_CONTRACTS_WITHOUT_TODOS'));


        $sheet->setTitle(JText::sprintf('COM_PROJECTS_MENU_MANAGER_STAT'));

        //Данные. Один проход цикла - одна строка
        $row = $start = 4; //Строка, с которой начнаются данные
        $j = 1; //Итерация строк
        foreach ($items['managers'] as $i => $manager) {
            $sheet->setCellValue("A{$row}", $j);
            $sheet->setCellValue("B{$row}", $manager);
            $sheet->setCellValue("C{$row}", $items['items'][$i]['status_2']['today']);
            $sheet->setCellValue("D{$row}", $items['items'][$i]['status_2']['dynamic']);
            $sheet->setCellValue("E{$row}", $items['items'][$i]['status_3']['today']);
            $sheet->setCellValue("F{$row}", $items['items'][$i]['status_3']['dynamic']);
            $sheet->setCellValue("G{$row}", $items['items'][$i]['status_4']['today']);
            $sheet->setCellValue("H{$row}", $items['items'][$i]['status_4']['dynamic']);
            $sheet->setCellValue("I{$row}", $items['items'][$i]['status_1']['today']);
            $sheet->setCellValue("J{$row}", $items['items'][$i]['status_1']['dynamic']);
            $sheet->setCellValue("K{$row}", $items['items'][$i]['status_9']['today']);
            $sheet->setCellValue("L{$row}", $items['items'][$i]['status_9']['dynamic']);
            $sheet->setCellValue("M{$row}", $items['items'][$i]['status_0']['today']);
            $sheet->setCellValue("N{$row}", $items['items'][$i]['status_0']['dynamic']);
            $sheet->setCellValue("O{$row}", $items['items'][$i]['exhibitors']['today']);
            $sheet->setCellValue("P{$row}", $items['items'][$i]['exhibitors']['dynamic']);
            $sheet->setCellValue("Q{$row}", $items['items'][$i]['cwt']);
            $row++;
            $j++;
        }

        $cells = array("C", "D", "E", "F", "G", "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q");
        $dynamic_colls = array("D", "F", "H", "J", "L", "N", "P", "Q");
        foreach ($cells as $cell) {
            for ($ii = $start; $ii < $row + 1; $ii ++) {
                if (!in_array($cell, $dynamic_colls)) {
                    $border_thin = array(
                        'borders' => array(
                            'top' => array(
                                'style' => PHPExcel_Style_Border::BORDER_THIN
                            ),
                            'bottom' => array(
                                'style' => PHPExcel_Style_Border::BORDER_THIN
                            ),
                            'left' => array(
                                'style' => PHPExcel_Style_Border::BORDER_THIN
                            ),
                            'right' => array(
                                'style' => PHPExcel_Style_Border::BORDER_THIN
                            ),
                        )
                    );
                    //$sheet->getStyle($cell . $ii)->applyFromArray($font);
                    continue;
                }
                $border_medium = array(
                    'borders' => array(
                        'top' => array(
                            'style' => PHPExcel_Style_Border::BORDER_THIN
                        ),
                        'bottom' => array(
                            'style' => PHPExcel_Style_Border::BORDER_THIN
                        ),
                        'left' => array(
                            'style' => PHPExcel_Style_Border::BORDER_THIN
                        ),
                        'right' => array(
                            'style' => PHPExcel_Style_Border::BORDER_MEDIUM
                        ),
                    )
                );
                $val = $sheet->getCell($cell . $ii)->getValue();
                if ($val == 0) $font = array(
                    "font" => array(
                        'color' => array('rgb' => ($cell == 'Q') ? '228B22' : '000000'),
                        'bold' => false
                    ),
                );
                if ($val > 0) $font = array(
                    "font" => array(
                        'color' => array('rgb' => ($cell != 'Q') ? '228B22' : 'E00D00'),
                        'bold' => false
                    ),
                );
                if ($val < 0) $font = array(
                    "font" => array(
                        'color' => array('rgb' => 'E00D00'),
                        'bold' => true
                    ),
                );
                $sheet->getStyle($cell . $ii)->applyFromArray($font);
            }
        }

        //Итого
        $sheet->getStyle("A{$row}")->applyFromArray(array(
                "alignment" => array(
                    'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT),
                "font" => array(
                    "bold" => true,
                ),
            )
        );
        $sheet->setCellValue("B{$row}", JText::sprintf('COM_PROJECTS_HEAD_CONTRACT_SUM'));
        $sheet->setCellValue("C{$row}", $items['total']['status_2']['today']);
        $sheet->setCellValue("D{$row}", $items['total']['status_2']['dynamic']);
        $sheet->setCellValue("E{$row}", $items['total']['status_3']['today']);
        $sheet->setCellValue("F{$row}", $items['total']['status_3']['dynamic']);
        $sheet->setCellValue("G{$row}", $items['total']['status_4']['today']);
        $sheet->setCellValue("H{$row}", $items['total']['status_4']['dynamic']);
        $sheet->setCellValue("I{$row}", $items['total']['status_1']['today']);
        $sheet->setCellValue("J{$row}", $items['total']['status_1']['dynamic']);
        $sheet->setCellValue("K{$row}", $items['total']['status_9']['today']);
        $sheet->setCellValue("L{$row}", $items['total']['status_9']['dynamic']);
        $sheet->setCellValue("M{$row}", $items['total']['status_0']['today']);
        $sheet->setCellValue("N{$row}", $items['total']['status_0']['dynamic']);
        $sheet->setCellValue("O{$row}", $items['total']['exhibitors']['today']);
        $sheet->setCellValue("P{$row}", $items['total']['exhibitors']['dynamic']);
        $sheet->setCellValue("Q{$row}", $items['total']['cwt']);
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

    /**
     * Возвращает массив с количеством сделок без задач
     * @return array
     * @since 2.0.2
     */
    private function getContractsWithoutTodosCount(): array
    {
        $db = $this->getDbo();
        $query = $db->getQuery(true);
        $query
            ->select("managerID, count(contractID) as cnt")
            ->from("`#__prj_contracts_without_todos`")
            ->group("managerID");
        // Фильтруем по проекту.
        $project = ProjectsHelper::getActiveProject();
        if (is_numeric($project)) {
            $project = (int)$project;
            $query->where("`prjID` = {$project}");
        }
        return $db->setQuery($query)->loadAssocList('managerID');
    }

    public function getDat(bool $for_excel = false): string
    {
        $dat = new DateTime($this->state->get('filter.dat'));
        $format = (!$for_excel) ? "d.m" : "d.m.Y";
        return $dat->format($format);
    }

    private $task, $return;
}
