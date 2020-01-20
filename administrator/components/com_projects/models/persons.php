<?php
defined('_JEXEC') or die;
use Joomla\CMS\MVC\Model\ListModel;

class ProjectsModelPersons extends ListModel
{
    public function __construct(array $config)
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = array(
                //Sorting                   //Filter        //Cross
                'id',                       'search',       'manager',
                'num',                      'exhibitorID',  'doc_status',
                'sort_amount, debt',        'activity',
                'dat',                      'rubric',
                'exhibitor',                'status',
                'todos',                    'cwt',
                'sort_amount, payments',    'country',
                'sort_amount, amount',
                'status_weight',
                'amount',
                'payment',
                'debt',
                'parent',
            );
        }

        $this->task = JFactory::getApplication()->input->getString('task', 'display');
        $this->return = ProjectsHelper::getReturnUrl();
        $this->userSettings = ProjectsHelper::getUserSettings();

        $this->titlesColumns = array( //Названия колонок для экспорта
            'num' => 'COM_PROJECTS_HEAD_CONTRACT_NUMBER_SHORT',
            'dat' => 'COM_PROJECTS_HEAD_CONTRACT_DATE_DOG',
            'stands' => 'COM_PROJECTS_HEAD_CONTRACT_STAND_SHORT',
            'project' => 'COM_PROJECTS_HEAD_CONTRACT_PROJECT',
            'exhibitor' => 'COM_PROJECTS_HEAD_CONTRACT_EXPONENT',
            'parent' => 'COM_PROJECTS_HEAD_CONTRACT_COEXP_BY',
            'todos' => 'COM_PROJECTS_HEAD_CONTRACT_ACTIVE_TODOS',
            'manager' => 'COM_PROJECTS_HEAD_CONTRACT_MANAGER',
            'status' => 'COM_PROJECTS_HEAD_CONTRACT_STATUS',
            'doc_status' => 'COM_PROJECTS_HEAD_CONTRACT_DOC_STATUS_SHORT',
            'amount' => 'COM_PROJECTS_HEAD_CONTRACT_AMOUNT',
            'payments' => 'COM_PROJECTS_HEAD_SCORE_PAYMENT',
            'debt' => 'COM_PROJECTS_HEAD_CONTRACT_DEBT',
        );

        parent::__construct($config);

        if (!empty($config['exhibitorID']) && is_numeric($config['exhibitorID'])) $this->exhibitorID = (int) $config['exhibitorID'];
    }

    protected function _getListQuery()
    {
        $db =& $this->getDbo();
        $query = $db->getQuery(true);
        $query
            ->select("*")
            ->from("#__prj_exp_persons p");

        $input = JFactory::getApplication()->input;

        /* Фильтр */
        if ($this->exhibitorID > 0) {
            $exhibitorID = $db->q($this->exhibitorID);
            $query->where("exbID = {$exhibitorID}");
        }

        /* Сортировка */
        $orderCol  = $this->state->get('list.ordering', 'p.id');
        $orderDirn = $this->state->get('list.direction', 'asc');
        $query->order($db->escape($orderCol . ' ' . $orderDirn));

        //Лимит
        $this->setState('list.limit', 0);

        return $query;
    }

    public function getItems()
    {
        $items = parent::getItems();
        $result = array('items' => array(), 'total' => array(), 'head' => $this->titlesColumns);

        foreach ($items as $item) {
            $arr = array();
            $arr['id'] = $item->id;
            $arr['exhibitorID'] = $item->exbID;
            $arr['dat'] = ($item->dat != null) ? JDate::getInstance($item->dat)->format("d.m.Y") : '';
            $arr['currency'] = $item->currency;
            $arr['project'] = $item->project;
            $arr['projectID'] = $item->projectID;
            $arr['exhibitor'] = $item->exhibitor;
            $arr['exhibitorID'] = $item->exhibitorID;
            $arr['isCoExp'] = $item->isCoExp;
            $arr['parentID'] = $item->parentID;
            $arr['parent'] = $item->parent;
            $arr['todos'] = $item->todos;
            $arr['manager'] = $this->prepareFio($item->manager);
            $arr['status'] = JText::sprintf($item->status);
            $arr['status_code'] = $item->status_code;
            $arr['doc_status'] = JText::sprintf("COM_PROJECTS_HEAD_CONTRACT_DOC_STATUS_". $item->doc_status);
            $arr['amount'] = (float) $item->amount;
            $arr['payments'] = (float) $item->payments;
            $arr['debt'] = (float) $item->debt;
            $arr['payerID'] = (float) $item->payerID;
            $arr['payer'] = (float) $item->payer;
            $result['items'][] = $this->prepare($arr);
        }

        if ($this->task != 'export') {
            //Итоговые суммы
            $projectID = ProjectsHelper::getActiveProject();
            if (is_numeric($projectID)) {
                $amounts = ProjectsHelper::getProjectAmount($projectID, $this->statusesAcceptContracts);
                $payments = ProjectsHelper::getProjectPayments($projectID, $this->statusesAcceptContracts);
                $debt = array();
                foreach ($amounts as $currency => $amount) {
                    $debt[$currency] = $amount ?? 0;
                    $amounts[$currency] = ProjectsHelper::getCurrency((float) $amount, $currency);
                }
                if (!isset($amounts['usd'])) $amounts['usd'] = ProjectsHelper::getCurrency((float) 0, 'usd');
                foreach ($payments as $currency => $payment) {
                    $debt[$currency] -= $payment ?? 0;
                    $payments[$currency] = ProjectsHelper::getCurrency((float) $payment, $currency);
                }
                foreach ($debt as $currency => $amount) $debt[$currency] = ProjectsHelper::getCurrency((float) $amount, $currency);
                $result['total']['amounts'] = $amounts;
                $result['total']['payments'] = $payments;
                $result['total']['debt'] = $debt;
            }
        }

        return $result;
    }

    public function export()
    {
        $items = $this->getItems();
        $items = $items['items'];
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
        header("Expires: Mon, 1 Apr 1974 05:00:00 GMT");
        header("Last-Modified: " . gmdate("D,d M YH:i:s") . " GMT");
        header("Cache-Control: no-cache, must-revalidate");
        header("Pragma: public");
        header("Content-type: application/vnd.ms-excel");
        header("Content-Disposition: attachment; filename=Contracts.xls");
        $objWriter = PHPExcel_IOFactory::createWriter($xls, 'Excel5');
        $objWriter->save('php://output');
        jexit();
    }

    /**
     * Возвращает стенды сделки
     * @param int $contractID ID сделки
     * @return array
     * @since 1.0.8.6
     */
    public function getStandsForContract(int $contractID): array
    {
        $stands = ProjectsHelper::getContractStands($contractID);
        $result = array();
        $tip = ProjectsHelper::getContractType($contractID);
        foreach ($stands as $stand) {
            $url = JRoute::_("index.php?option=com_projects&amp;task=stand.edit&amp;id={$stand->id}&amp;contractID={$stand->contractID}&amp;return={$this->return}");
            if ($this->task != 'export') {
                $result[] = ($contractID != $stand->contractID && $tip == 0) ? $stand->number : JHtml::link($url, ($tip == 0) ? $stand->number : $stand->title);
            }
            else {
                $result[] = $stand->number;
            }
        }
        return $result;
    }

    /**
     * Возвращает количество столбцов на странице
     * @return int
     * @since 1.2.6.0
     */
    public function getColumnsCount(): int
    {
        $cnt = 13;
        $columns = array('parent', 'manager', 'doc_status', 'id'); //Опциональные колонки
        foreach ($columns as $column) {
            if ($this->userSettings["contracts_v2-column_{$column}"]) $cnt++;
        }
        return $cnt;
    }

    /**
     * Возвращает ID сделок без задач
     * @return array
     * @since 2.0.2
     */
    private function getContractsWithoutTodosCount(): array
    {
        $db = $this->getDbo();
        $query = $db->getQuery(true);
        $query
            ->select("contractID")
            ->from("`#__prj_contracts_without_todos`");
        return $db->setQuery($query)->loadColumn();
    }


    /* Сортировка по умолчанию */
    protected function populateState($ordering = null, $direction = null)
    {
        $search = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search', '', 'string');
        $this->setState('filter.search', $search);
        $manager = $this->getUserStateFromRequest($this->context . '.filter.manager', 'filter_manager', '', 'string');
        $this->setState('filter.manager', $manager);
        $status = $this->getUserStateFromRequest($this->context . '.filter.status', 'filter_status', '', 'string');
        $this->setState('filter.status', $status);
        $currency = $this->getUserStateFromRequest($this->context . '.filter.currency', 'filter_currency', '', 'string');
        $this->setState('filter.currency', $currency);
        $activity = $this->getUserStateFromRequest($this->context . '.filter.activity', 'filter_activity', '', 'string');
        $this->setState('filter.state', $activity);
        $rubric = $this->getUserStateFromRequest($this->context . '.filter.rubric', 'filter_rubric', '', 'string');
        $this->setState('filter.rubric', $rubric);
        $doc_status = $this->getUserStateFromRequest($this->context . '.filter.doc_status', 'filter_doc_status');
        $this->setState('filter.doc_status', $doc_status);
        $cwt = $this->getUserStateFromRequest($this->context . '.filter.cwt', 'filter_cwt');
        $this->setState('filter.cwt', $cwt);
        $country = $this->getUserStateFromRequest($this->context . '.filter.country', 'filter_country');
        $this->setState('filter.country', $country);

        parent::populateState('plan_dat', 'asc');
    }

    protected function getStoreId($id = '')
    {
        $id .= ':' . $this->getState('filter.search');
        $id .= ':' . $this->getState('filter.manager');
        $id .= ':' . $this->getState('filter.status');
        $id .= ':' . $this->getState('filter.currency');
        $id .= ':' . $this->getState('filter.activity');
        $id .= ':' . $this->getState('filter.rubric');
        $id .= ':' . $this->getState('filter.doc_status');
        $id .= ':' . $this->getState('filter.cwt');
        $id .= ':' . $this->getState('filter.country');
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

    /**
     * Возвращает полное ФИО или без отчества в соответствии с настройками
     * @param string $fio полное ФИО
     * @return string вариант ФИО из настроек
     * @since 1.2.6.0
     */
    private function prepareFio(string $fio): string
    {
        $result = $fio;
        if (!$this->userSettings['contracts_v2-show_full_manager_fio']) {
            $arr = explode(' ', $fio);
            $result = sprintf("%s %s", $arr[0], $arr[1]);
        }
        return $result;
    }

    private $task, $return, $userSettings, $statusesAcceptContracts, $titlesColumns, $exhibitorID;
}
