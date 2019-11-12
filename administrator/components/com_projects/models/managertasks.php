<?php
defined('_JEXEC') or die;

use Joomla\CMS\MVC\Model\ListModel;

class ProjectsModelManagertasks extends ListModel
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
        $this->type = JFactory::getApplication()->input->getString('type', '');
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

        foreach ($items as $item) {
            if ($item->dat == $curdate) {
                $result['items'][$item->managerID]['todos_expires']['today'] = (int)$item->todos_expires;
                if (!isset($result['total']['todos_expires']['today'])) $result['total']['todos_expires']['today'] = 0;
                $result['total']['todos_expires']['today'] += (int)$item->todos_expires;

                $result['items'][$item->managerID]['todos_plan']['today'] = (int)$item->todos_plan;
                if (!isset($result['total']['todos_plan']['today'])) $result['total']['todos_plan']['today'] = 0;
                $result['total']['todos_plan']['today'] += (int)$item->todos_plan;

                $result['items'][$item->managerID]['todos_future']['today'] = (int)$item->todos_future;
                if (!isset($result['total']['todos_future']['today'])) $result['total']['todos_future']['today'] = 0;
                $result['total']['todos_future']['today'] += (int)$item->todos_future;

                $result['items'][$item->managerID]['todos_completed']['today'] = (int)$item->todos_completed;
                if (!isset($result['total']['todos_completed']['today'])) $result['total']['todos_completed']['today'] = 0;
                $result['total']['todos_completed']['today'] += (int)$item->todos_completed;
            }
            else {
                $result['items'][$item->managerID]['todos_expires'][$item->dat] = (int)$item->todos_expires;
                $result['items'][$item->managerID]['todos_expires']['dynamic'] = (int)$result['items'][$item->managerID]['todos_expires']['today'] - (int)$item->todos_expires;
                if (!isset($result['total']['todos_expires']['dynamic'])) $result['total']['todos_expires']['dynamic'] = 0;
                $result['total']['todos_expires']['dynamic'] += (int)$result['items'][$item->managerID]['todos_expires']['dynamic'];

                $result['items'][$item->managerID]['todos_plan'][$item->dat] = (int)$item->todos_plan;
                $result['items'][$item->managerID]['todos_plan']['dynamic'] = (int)$result['items'][$item->managerID]['todos_plan']['today'] - (int)$item->todos_plan;
                if (!isset($result['total']['todos_plan']['dynamic'])) $result['total']['todos_plan']['dynamic'] = 0;
                $result['total']['todos_plan']['dynamic'] += (int)$result['items'][$item->managerID]['todos_plan']['dynamic'];

                $result['items'][$item->managerID]['todos_future'][$item->dat] = (int)$item->todos_future;
                $result['items'][$item->managerID]['todos_future']['dynamic'] = (int)$result['items'][$item->managerID]['todos_future']['today'] - (int)$item->todos_future;
                if (!isset($result['total']['todos_future']['dynamic'])) $result['total']['todos_future']['dynamic'] = 0;
                $result['total']['todos_future']['dynamic'] += (int)$result['items'][$item->managerID]['todos_future']['dynamic'];

                $result['items'][$item->managerID]['todos_completed'][$item->dat] = (int)$item->todos_completed;
                $result['items'][$item->managerID]['todos_completed']['dynamic'] = (int)$result['items'][$item->managerID]['todos_completed']['today'] - (int)$item->todos_completed;
                if (!isset($result['total']['todos_completed']['dynamic'])) $result['total']['todos_completed']['dynamic'] = 0;
                $result['total']['todos_completed']['dynamic'] += (int)$result['items'][$item->managerID]['todos_completed']['dynamic'];
            }
            if (!isset($result['managers'][$item->managerID])) $result['managers'][$item->managerID] = $item->manager;
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
        $merge = array("A1:Q1", "A2:A3", "B2:B3", "C2:D2", "E2:F2", "G2:H2", "I2:J2");
        foreach ($merge as $value) {
            $sheet->mergeCells($value);
        }

        //Выравнивание
        $alignment = array("A1", "B2", "C2", "E2", "G2", "I2");
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
            "I" => 10, "J" => 10);
        foreach ($width as $col => $value) {
            $sheet->getColumnDimension($col)->setWidth($value);
        }

        //Формат данных
        $cells = array("C3", "E3", "G3", "I3");
        foreach ($cells as $cell) {
            $sheet->getStyle($cell)->applyFromArray(
                array(
                    "numberformat" => array(
                        'code' => PHPExcel_Style_NumberFormat::FORMAT_DATE_DMMINUS
                    ),
                ),
            );
        }

        $sheet->setCellValue("A1", JText::sprintf('COM_PROJECTS_REPORT_TITLE_TASKS'));
        $sheet->setCellValue("A2", "№");
        $sheet->setCellValue("B2", JText::sprintf('COM_PROJECTS_HEAD_MANAGER_STAT_MANAGER'));
        $sheet->setCellValue("C2", JText::sprintf('COM_PROJECTS_HEAD_MANAGER_TASKS_TODOS_EXPIRES'));
        $sheet->setCellValue("E2", JText::sprintf('COM_PROJECTS_HEAD_MANAGER_TASKS_TODOS_PLAN'));
        $sheet->setCellValue("G2", JText::sprintf('COM_PROJECTS_HEAD_MANAGER_TASKS_TODOS_FUTURE'));
        $sheet->setCellValue("I2", JText::sprintf('COM_PROJECTS_HEAD_MANAGER_TASKS_TODOS_COMPLETED'));

        $sheet->setCellValue("C3", $dat);
        $sheet->setCellValue("D3", JText::sprintf('COM_PROJECTS_HEAD_MANAGER_STAT_DYNAMIC'));
        $sheet->setCellValue("E3", $dat);
        $sheet->setCellValue("F3", JText::sprintf('COM_PROJECTS_HEAD_MANAGER_STAT_DYNAMIC'));
        $sheet->setCellValue("G3", $dat);
        $sheet->setCellValue("H3", JText::sprintf('COM_PROJECTS_HEAD_MANAGER_STAT_DYNAMIC'));
        $sheet->setCellValue("I3", $dat);
        $sheet->setCellValue("J3", JText::sprintf('COM_PROJECTS_HEAD_MANAGER_STAT_DYNAMIC'));


        $sheet->setTitle(JText::sprintf('COM_PROJECTS_REPORT_TITLE_TASKS'));

        //Данные. Один проход цикла - одна строка
        $row = $start = 4; //Строка, с которой начнаются данные
        $j = 1; //Итерация строк
        foreach ($items['managers'] as $i => $manager) {
            $sheet->setCellValue("A{$row}", $j);
            $sheet->setCellValue("B{$row}", $manager);
            $sheet->setCellValue("C{$row}", $items['items'][$i]['todos_expires']['today']);
            $sheet->setCellValue("D{$row}", $items['items'][$i]['todos_expires']['dynamic']);
            $sheet->setCellValue("E{$row}", $items['items'][$i]['todos_plan']['today']);
            $sheet->setCellValue("F{$row}", $items['items'][$i]['todos_plan']['dynamic']);
            $sheet->setCellValue("G{$row}", $items['items'][$i]['todos_future']['today']);
            $sheet->setCellValue("H{$row}", $items['items'][$i]['todos_future']['dynamic']);
            $sheet->setCellValue("I{$row}", $items['items'][$i]['todos_completed']['today']);
            $sheet->setCellValue("J{$row}", $items['items'][$i]['todos_completed']['dynamic']);
            $row++;
            $j++;
        }

        $cells = array("C", "D", "E", "F", "G", "H", "I", "J");
        $dynamic_colls = array("D", "F", "H", "J");
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
        $sheet->setCellValue("C{$row}", $items['total']['todos_expires']['today']);
        $sheet->setCellValue("D{$row}", $items['total']['todos_expires']['dynamic']);
        $sheet->setCellValue("E{$row}", $items['total']['todos_plan']['today']);
        $sheet->setCellValue("F{$row}", $items['total']['todos_plan']['dynamic']);
        $sheet->setCellValue("G{$row}", $items['total']['todos_future']['today']);
        $sheet->setCellValue("H{$row}", $items['total']['todos_future']['dynamic']);
        $sheet->setCellValue("I{$row}", $items['total']['todos_completed']['today']);
        $sheet->setCellValue("J{$row}", $items['total']['todos_completed']['dynamic']);

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

    public function getDat(bool $for_excel = false): string
    {
        $dat = new DateTime($this->state->get('filter.dat'));
        $format = (!$for_excel) ? "d.m" : "d.m.Y";
        return $dat->format($format);
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

    public function getType()
    {
        return $this->type;
    }

    private $task, $return, $type;
}
