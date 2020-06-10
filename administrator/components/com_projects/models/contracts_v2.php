<?php
defined('_JEXEC') or die;
use Joomla\CMS\MVC\Model\ListModel;

class ProjectsModelContracts_v2 extends ListModel
{
    public function __construct(array $config)
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = array(
                //Sorting                   //Filter        //Cross
                'id',                       'search',       'manager',
                'num',                      'currency',     'doc_status',
                'sort_amount, debt',        'activity',
                'dat',                      'rubric',
                'exhibitor',                'status',
                'todos',                    'cwt',
                'sort_amount, payments',    'country',
                'sort_amount, amount',      'info_catalog',
                'status_weight',            'logo_catalog',
                'amount',                   'pvn_1',
                'payment',                  'pvn_1a',
                'debt',                     'pvn_1b',
                'parent',                   'pvn_1v',
                                            'pvn_1g',
                                            'no_exhibit',
            );
        }

        $this->task = JFactory::getApplication()->input->getString('task', 'display');
        $this->return = ProjectsHelper::getReturnUrl();
        $this->userSettings = ProjectsHelper::getUserSettings();

        $this->statusesAcceptContracts = array(1, 2, 3, 4, 10); //Статусы для подсчёта итоговой суммы
        $this->titlesColumns = array( //Названия колонок для экспорта
            'num' => 'COM_PROJECTS_HEAD_CONTRACT_NUMBER_SHORT',
            'dat' => 'COM_PROJECTS_HEAD_CONTRACT_DATE_DOG',
            'stands' => 'COM_PROJECTS_HEAD_CONTRACT_STAND_SHORT',
            'project' => 'COM_PROJECTS_HEAD_CONTRACT_PROJECT',
            'exhibitor' => 'COM_PROJECTS_HEAD_CONTRACT_EXPONENT',
            'todos' => 'COM_PROJECTS_HEAD_CONTRACT_ACTIVE_TODOS',
            'manager' => 'COM_PROJECTS_HEAD_CONTRACT_MANAGER',
            'status' => 'COM_PROJECTS_HEAD_CONTRACT_STATUS',
            'parent' => 'COM_PROJECTS_HEAD_CONTRACT_COEXP_BY',
            'doc_status' => 'COM_PROJECTS_HEAD_CONTRACT_DOC_STATUS_SHORT',
            'amount' => 'COM_PROJECTS_HEAD_CONTRACT_AMOUNT',
            'payments' => 'COM_PROJECTS_HEAD_SCORE_PAYMENT',
            'debt' => 'COM_PROJECTS_HEAD_CONTRACT_DEBT',
            'info_catalog' => 'COM_PROJECTS_HEAD_CONTRACT_INFO_CATALOG',
            'logo_catalog' => 'COM_PROJECTS_HEAD_CONTRACT_LOGO_CATALOG',
            'pvn_1' => 'COM_PROJECTS_HEAD_CONTRACT_PVN_1',
            'pvn_1a' => 'COM_PROJECTS_HEAD_CONTRACT_PVN_1A',
            'pvn_1b' => 'COM_PROJECTS_HEAD_CONTRACT_PVN_1B',
            'pvn_1v' => 'COM_PROJECTS_HEAD_CONTRACT_PVN_1V',
            'pvn_1g' => 'COM_PROJECTS_HEAD_CONTRACT_PVN_1G',
            'no_exhibit' => 'COM_PROJECTS_HEAD_CONTRACT_NO_EXHIBIT',
            'info_arrival' => 'COM_PROJECTS_HEAD_CONTRACT_INFO_ARRIVAL',
        );

        parent::__construct($config);
    }

    protected function _getListQuery()
    {
        $db = $this->getDbo();
        $query = $db->getQuery(true);
        $query
            ->select("*")
            ->from("`#__prj_contracts_v2`");

        $input = JFactory::getApplication()->input;

        /* Фильтр */
        $search = $this->getState('filter.search');
        if (!empty($search))
        {
            $search = trim($search);
            if (strpos($search, '№') !== false || strpos($search, '#') !== false) {
                $search = str_ireplace(array("№",'#'), '', $search);
                $search = $db->q($search);
                $query->where("(`num` LIKE {$search})");
            }
            else {
                if (stripos($search, 'id:') !== false) {
                    $cid = explode(":", $search);
                    $cid = $cid[1];
                    $query->where("id = {$db->q($cid)}");
                }
                else {
                    $search = $db->q("%{$search}%");
                    $query->where("(`title_ru_short` LIKE {$search} OR `title_ru_full` LIKE {$search} OR `title_en` LIKE {$search})");
                }
            }
        }
        $no_exhibit = $this->getState('filter.no_exhibit');
        if (is_numeric($no_exhibit)) {
            $query->where("no_exhibit = {$db->q($no_exhibit)}");
        }

        // Фильтруем по статусу присланного договора.
        $doc_status = $this->getState('filter.doc_status');
        if (is_numeric($doc_status)) {
            $doc_status = (int) $doc_status;
            $query->where("`doc_status` = {$doc_status}");
        }

        // Фильтруем по валюте.
        $currency = $this->getState('filter.currency');
        if (!empty($currency))
        {
            $currency = $db->q($currency);
            $query->where("`currency` LIKE {$currency}");
        }

        // Фильтруем по стране.
        $country = $this->getState('filter.country');
        if (!empty($country))
        {
            $country = $db->q($country);
            $query
                ->where("`countryID` LIKE {$country}");
        }

        // Фильтруем по менеджеру.
        $manager = $this->getState('filter.manager');
        if (is_numeric($manager)) {
            $manager = (int) $manager;
            $query->where("`managerID` = {$manager}");
        }
        else {
            $manager = $input->getInt('managerID', 0);
            if ($manager > 0) {
                $query->where("`managerID` = {$manager}");
            }
        }

        // Фильтруем по видам деятельности.
        $act = $this->getState('filter.activity');
        if (is_numeric($act)) {
            if ($act > 0) {
                $exponents = ProjectsHelper::getExponentsInActivities($act);
                if (!empty($exponents)) {
                    $exponents = implode(', ', $exponents);
                    $query->where("`exhibitorID` IN ({$exponents})");
                }
            }
            else {
                $query->where("exhibitorID not in (select distinct exbID from `#__prj_exp_act`)");
            }
        }

        //Фильтруем по тематикам разделов
        $rubric = $this->getState('filter.rubric');
        if (is_numeric($rubric)) {
            if ($rubric != -1) {
                $ids = ProjectsHelper::getRubricContracts($rubric);
                if (!empty($ids)) {
                    $ids = implode(', ', $ids);
                    $query->where("`id` IN ({$ids})");
                } else {
                    $query->where("`id` = 0");
                }
            }
            else {
                $ids = ProjectsHelper::getRubricContracts();
                if (!empty($ids)) {
                    $ids = implode(', ', $ids);
                    $query->where("`id` NOT IN ({$ids})");
                }
            }
        }

        // Фильтруем по статусу.
        $status = $this->getState('filter.status');
        if (is_array($status)) {
            if (!empty($status)) {
                $statuses = implode(', ', $status);
                $query->where("`status_code` IN ({$statuses})");
                $this->statusesAcceptContracts = $status;
            }
            else
            {
                $query->where("`status_code` IS NOT NULL");
            }
        }

        //Поиск по ID проекта (из URL)
        $projectID = $input->getString('projectID', '');
        if (is_numeric(($projectID))) {
            $projectID = (int) $projectID;
            $query->where("`projectID` = {$projectID}");
        }
        else {
            // Фильтруем по проекту.
            $project = ProjectsHelper::getActiveProject();
            if (is_numeric($project)) {
                $project = (int) $project;
                $query->where("`projectID` = {$project}");
            }
        }

        //Поиск по ID экспонента (из URL)
        $exhibitorID = $input->getString('exhibitorID', '');
        if (is_numeric(($exhibitorID))) {
            $exhibitorID = (int) $exhibitorID;
            $query->where("`exhibitorID` = {$exhibitorID}");
        }

        //Поиск по заполненности форм
        $info_catalog = $this->getState('filter.info_catalog');
        if (is_numeric($info_catalog)) {
            $query->where("info_catalog = {$db->q($info_catalog)}");
        }
        $logo_catalog = $this->getState('filter.logo_catalog');
        if (is_numeric($logo_catalog)) {
            $query->where("logo_catalog = {$db->q($logo_catalog)}");
        }
        $pvn_1 = $this->getState('filter.pvn_1');
        if (is_numeric($pvn_1)) {
            $query->where("pvn_1 = {$db->q($pvn_1)}");
        }
        $pvn_1a = $this->getState('filter.pvn_1a');
        if (is_numeric($pvn_1a)) {
            $query->where("pvn_1a = {$db->q($pvn_1a)}");
        }
        $pvn_1b = $this->getState('filter.pvn_1b');
        if (is_numeric($pvn_1b)) {
            $query->where("pvn_1b = {$db->q($pvn_1b)}");
        }
        $pvn_1v = $this->getState('filter.pvn_1v');
        if (is_numeric($pvn_1v)) {
            $query->where("pvn_1v = {$db->q($pvn_1v)}");
        }
        $pvn_1g = $this->getState('filter.pvn_1g');
        if (is_numeric($pvn_1g)) {
            $query->where("pvn_1g = {$db->q($pvn_1g)}");
        }

        //Поиск по сделкам без задач
        $cwt = $input->getInt('cwt', 0);
        if ($cwt == 1) {
            $ids = $this->getContractsWithoutTodosCount();
            if (!empty($ids)) {
                $ids = implode(", ", $ids);
                $query->where("`id` IN ({$ids})");
            }
        } else {
            $cwt = $this->getState('filter.cwt');
            if (is_numeric($cwt) && $cwt == 1) {
                $ids = $this->getContractsWithoutTodosCount();
                if (!empty($ids)) {
                    $ids = implode(", ", $ids);
                    $query->where("`id` IN ({$ids})");
                }
            }
        }

        //Показываем только свои сделки, но если только неактивны фильтры по видам деятельности и тематической рубрике
        if (!ProjectsHelper::canDo('projects.access.contracts.full'))
        {
            if (!is_numeric($act) && !is_numeric($rubric) && (!is_numeric($manager) || $manager == 0)) {
                $userID = JFactory::getUser()->id;
                $query->where("`managerID` = {$userID}");
            }
        }

        /* Сортировка */
        $orderCol  = $this->state->get('list.ordering', 'plan_dat');
        $orderDirn = $this->state->get('list.direction', 'asc');
        if ($orderCol == 'num') {
            if ($orderDirn == 'ASC') $orderCol = 'LENGTH(num), num';
            if ($orderDirn == 'DESC') $orderCol = 'LENGTH(num) desc, num';
        }
        $query->order($db->escape($orderCol . ' ' . $orderDirn));

        //Лимит
        $limit = $this->userSettings['general_limit'];
        if (is_numeric($limit)) {
            $limit = (int) $limit;
            $from_state = $this->getState('list.limit');
            $this->setState('list.limit', $limit);
            if ($limit != $from_state)
            {
                $this->setState('list.limit', $limit);
            }
        }
        if ($this->task == 'export') {
            $this->setState('list.limit', 0);
        }

        return $query;
    }

    public function getItems()
    {
        $items = parent::getItems();
        $result = array('items' => array(), 'total' => array(), 'head' => $this->titlesColumns);

        foreach ($items as $item) {
            $arr = array();
            $arr['id'] = $item->id;
            $arr['num'] = $item->num;
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
            $arr['info_catalog'] = JText::sprintf(($item->info_catalog != '1') ? 'JNO' : 'JYES');
            $arr['logo_catalog'] = JText::sprintf(($item->logo_catalog != '1') ? 'JNO' : 'JYES');
            $arr['pvn_1'] = JText::sprintf(($item->pvn_1 != '1') ? 'JNO' : 'JYES');
            $arr['pvn_1a'] = JText::sprintf(($item->pvn_1a != '1') ? 'JNO' : 'JYES');
            $arr['pvn_1b'] = JText::sprintf(($item->pvn_1b != '1') ? 'JNO' : 'JYES');
            $arr['pvn_1v'] = JText::sprintf(($item->pvn_1v != '1') ? 'JNO' : 'JYES');
            $arr['pvn_1g'] = JText::sprintf(($item->pvn_1g != '1') ? 'JNO' : 'JYES');
            $arr['no_exhibit'] = JText::sprintf(($item->no_exhibit != '1') ? 'JNO' : 'JYES');
            $arr['info_arrival'] = JText::sprintf(($item->info_arrival != '1') ? 'JNO' : 'JYES');

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
        $sheet->getColumnDimension('F')->setWidth(13);
        $sheet->getColumnDimension('G')->setWidth(35);
        $sheet->getColumnDimension('H')->setWidth(8);
        $sheet->getColumnDimension('I')->setWidth(30);
        $sheet->getColumnDimension('J')->setWidth(8);
        $sheet->getColumnDimension('K')->setWidth(19);
        $sheet->getColumnDimension('L')->setWidth(19);
        $sheet->getColumnDimension('M')->setWidth(19);
        $sheet->getColumnDimension('N')->setWidth(21);
        $sheet->getColumnDimension('O')->setWidth(10);
        $sheet->getColumnDimension('P')->setWidth(11);
        $sheet->getColumnDimension('Q')->setWidth(11);
        $sheet->getColumnDimension('R')->setWidth(11);
        $sheet->getColumnDimension('S')->setWidth(11);
        $sheet->getColumnDimension('T')->setWidth(11);
        $sheet->getColumnDimension('U')->setWidth(11);
        $sheet->getColumnDimension('V')->setWidth(11);

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
        $sheet->getStyle('N1')->getFont()->setBold(true);
        $sheet->getStyle('O1')->getFont()->setBold(true);
        $sheet->getStyle('P1')->getFont()->setBold(true);
        $sheet->getStyle('Q1')->getFont()->setBold(true);
        $sheet->getStyle('R1')->getFont()->setBold(true);
        $sheet->getStyle('S1')->getFont()->setBold(true);
        $sheet->getStyle('T1')->getFont()->setBold(true);
        $sheet->getStyle('U1')->getFont()->setBold(true);
        $sheet->getStyle('V1')->getFont()->setBold(true);
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
        $info_catalog = $this->getUserStateFromRequest($this->context . '.filter.info_catalog', 'filter_info_catalog');
        $this->setState('filter.info_catalog', $info_catalog);
        $logo_catalog = $this->getUserStateFromRequest($this->context . '.filter.logo_catalog', 'filter_logo_catalog');
        $this->setState('filter.logo_catalog', $logo_catalog);
        $pvn_1 = $this->getUserStateFromRequest($this->context . '.filter.pvn_1', 'filter_pvn_1');
        $this->setState('filter.pvn_1', $pvn_1);
        $pvn_1a = $this->getUserStateFromRequest($this->context . '.filter.pvn_1a', 'filter_pvn_1a');
        $this->setState('filter.pvn_1a', $pvn_1a);
        $pvn_1b = $this->getUserStateFromRequest($this->context . '.filter.pvn_1b', 'filter_pvn_1b');
        $this->setState('filter.pvn_1b', $pvn_1b);
        $pvn_1v = $this->getUserStateFromRequest($this->context . '.filter.pvn_1v', 'filter_pvn_1v');
        $this->setState('filter.pvn_1v', $pvn_1v);
        $pvn_1g = $this->getUserStateFromRequest($this->context . '.filter.pvn_1g', 'filter_pvn_1g');
        $this->setState('filter.pvn_1g', $pvn_1g);
        $no_exhibit = $this->getUserStateFromRequest($this->context . '.filter.no_exhibit', 'filter_no_exhibit');
        $this->setState('filter.no_exhibit', $no_exhibit);

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
        $id .= ':' . $this->getState('filter.info_catalog');
        $id .= ':' . $this->getState('filter.logo_catalog');
        $id .= ':' . $this->getState('filter.pvn_1');
        $id .= ':' . $this->getState('filter.pvn_1a');
        $id .= ':' . $this->getState('filter.pvn_1b');
        $id .= ':' . $this->getState('filter.pvn_1v');
        $id .= ':' . $this->getState('filter.pvn_1g');
        $id .= ':' . $this->getState('filter.no_exhibit');
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

    private $task, $return, $userSettings, $statusesAcceptContracts, $titlesColumns;
}
