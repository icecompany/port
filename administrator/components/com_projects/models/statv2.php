<?php
defined('_JEXEC') or die;

use Joomla\CMS\MVC\Model\ListModel;
use Joomla\CMS\MVC\Model\AdminModel;

class ProjectsModelStatv2 extends ListModel
{
    public $itemID;

    public function __construct(array $config)
    {
        $this->itemID = JFactory::getApplication()->input->getInt('itemID', 0);
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = array(
                'search',
                'project',
                'status',
                'manager',
            );
        }

        parent::__construct($config);
    }

    protected function _getListQuery()
    {
        $this->setState('list.limit', '0');
        $db =& $this->getDbo();
        $query = $db->getQuery(true);
        $query
            ->select("s.itemID, s.currency, sum(s.value) as `val`, sum(s.price) as `price`")
            ->select("i.title_ru as `item`, i.unit")
            ->from("`#__prj_stat_v2` s")
            ->leftJoin("`#__prj_contracts` c on c.id = s.contractID")
            ->leftJoin("`s7vi9_prc_items` i on i.id = s.itemID");

        if ($this->itemID != 0) {
            $query
                ->select("s.contractID, s.managerID, e.exhibitor, c.number as `contract`")
                ->leftJoin("`#__prj_exhibitors_all` e on e.id = s.expID")
                ->where("s.itemID = {$this->itemID}")
                ->group("s.currency, s.contractID");
        }
        else {
            $query
                ->select("i.application")
                ->group("s.itemID, s.currency");
        }
        // Фильтруем по менеджеру.
        $manager = (!ProjectsHelper::canDo('projects.access.stat.full')) ? JFactory::getUser()->id : $this->getState('filter.manager');
        if (is_numeric($manager)) {
            $query->where("c.managerID = {$manager}");
        }

        /* Фильтр */
        $search = $this->getState('filter.search');
        if (!empty($search)) {
            $search = $query->q("%{$search}%");
            $query->where("i.title_ru LIKE {$search}");
        }
        // Фильтруем по проекту.
        $project = $this->getState('filter.project');
        if (empty($project)) $project = ProjectsHelper::getActiveProject();
        if (is_numeric($project)) {
            $query->where('s.prjID = ' . (int) $project);
            $session = JFactory::getSession();
            $session->set('projectID', $project);
        }
        // Фильтруем по статусу.
        $status = $this->getState('filter.status');
        if (is_array($status)) {
            if (!empty($status)) {
                $statuses = implode(', ', $status);
                if ($status[0] != '0') {
                    $query->where("`s`.`status` IN ({$statuses})");
                }
                else
                {
                    $this->state->set('filter.status', '');
                    $query->where("`s`.`status` IN (1,2,3,4,10)");
                }
            }
        }
        else {
            $query->where("`s`.`status` IN (1,2,3,4,10)");
        }

        /* Сортировка */
        $orderCol = $this->state->get('list.ordering');
        $orderDirn = $this->state->get('list.direction');
        $query->order($db->escape($orderCol . ' ' . $orderDirn));

        return $query;
    }

    public function getItems()
    {
        $items = parent::getItems();
        $result = array();
        $result['amount'] = array('rub' => 0, 'usd' => 0, 'eur' => 0);
        $result['sum'] = array('rub' => 0, 'usd' => 0, 'eur' => 0);
        if ($this->itemID != 0) $result['cnt'] = 0;
        $result['items'] = array();
        $xls = (JFactory::getApplication()->input->getString('task') == 'exportxls');
        foreach ($items as $item) {
            if ($this->itemID != 0) {
                $return = ProjectsHelper::getReturnUrl();
                if (!isset($result['items'][$item->contractID])) $result['items'][$item->contractID] = array();
                $url = JRoute::_("index.php?option=com_projects&amp;task=exhibitor.edit&amp;id={$item->expID}&amp;return={$return}");
                $result['items'][$item->contractID]['exhibitor'] = (!$xls) ? JHtml::link($url, $item->exhibitor) : $item->exhibitor;
                $url = JRoute::_("index.php?option=com_projects&amp;task=contract.edit&amp;id={$item->contractID}&amp;return={$return}");
                $title = ($item->contract != null) ? JText::sprintf('COM_PROJECTS_TITLE_CONTRACT_WITH_NUMBER', $item->contract) : JText::sprintf('COM_PROJECTS_TITLE_CONTRACT_WITHOUT_NUMBER');
                $result['items'][$item->contractID]['contract'] = (!$xls) ? JHtml::link($url, $title) : $title;
                $stands = $this->getStands($item->contractID);
                $result['items'][$item->contractID]['stands'] = implode(' ', $stands);
                if (!isset($result['items'][$item->contractID]['value'])) $result['items'][$item->contractID]['value'] = 0;
                $result['items'][$item->contractID]['value'] += $item->val ?? 0;
                $result['items'][$item->contractID]['unit'] = ProjectsHelper::getUnit($item->unit);
                $result['items'][$item->contractID]['price'][$item->currency] = ProjectsHelper::getCurrency((float) $item->price ?? 0, $item->currency);
            } else {
                if (!isset($result['items'][$item->itemID])) $result['items'][$item->itemID] = array();
                $url = JRoute::_("index.php?option=com_projects&amp;view=statv2&amp;itemID={$item->itemID}");
                $options = array('class' => 'small');
                $result['items'][$item->itemID]['title'] = (!$xls) ? JHtml::link($url, $item->item, $options) : $item->item;
                $result['items'][$item->itemID]['application'] = ProjectsHelper::getApplication($item->application);
                $result['items'][$item->itemID]['unit'] = ProjectsHelper::getUnit($item->unit);
                if(!isset($result['items'][$item->itemID]['value'])) $result['items'][$item->itemID]['value'] = (float) 0;
                $result['items'][$item->itemID]['value'] += $item->val ?? 0;
                $result['items'][$item->itemID]['price'][$item->currency] = ProjectsHelper::getCurrency((float) $item->price ?? 0, $item->currency);
                if(!isset($result['sum'][$item->currency])) $result['sum'][$item->currency] = 0;
            }
            $result['sum'][$item->currency] += $item->price ?? 0;
        }
        return $result;
    }

    public function getPriceItem(): string
    {
        if ($this->itemID == 0) return '';
        $im = AdminModel::getInstance('Item', 'ProjectsModel');
        $item = $im->getItem($this->itemID);
        return $item->title_ru ?? '';
    }

    private function getStands(int $contractID): array
    {
        $xls = (JFactory::getApplication()->input->getString('task') == 'exportxls');
        $stands = ProjectsHelper::getContractStands($contractID);
        $result = array();
        $return = ProjectsHelper::getReturnUrl();
        foreach ($stands as $stand) {
            $url = JRoute::_("index.php?option=com_projects&amp;task=stand.edit&amp;contractID={$contractID}&amp;id={$stand->id}&amp;return={$return}");
            $result[] = (!$xls) ? JHtml::link($url, $stand->number) : $stand->number;
        }
        return $result;
    }

    public function exportToExcel()
    {
        if (is_array($this->state->get('filter.items'))) return;
        $items = $this->getItems();
        $data = $items['items'];
        JLoader::discover('PHPExcel', JPATH_LIBRARIES);
        JLoader::register('PHPExcel', JPATH_LIBRARIES . '/PHPExcel.php');
        $xls = new PHPExcel();
        $xls->setActiveSheetIndex(0);
        $sheet = $xls->getActiveSheet();
        $sheet->setTitle(JText::sprintf('COM_PROJECTS_MENU_STAT'));
        if ($this->itemID != 0) {
            for ($i = 1; $i < count($data) + 1; $i++) {
                for ($j = 0; $j < 12; $j++) {
                    if ($i == 1) {
                        if ($j == 0) $sheet->setCellValueByColumnAndRow($j, $i, JText::sprintf('COM_PROJECTS_HEAD_ITEM_TITLE'));
                        if ($j == 1) $sheet->setCellValueByColumnAndRow($j, $i, JText::sprintf('COM_PROJECTS_HEAD_PAYMENT_CONTRACT_DESC'));
                        if ($j == 2) $sheet->setCellValueByColumnAndRow($j, $i, JText::sprintf('COM_PROJECTS_BLANK_STANDS'));
                        if ($j == 3) $sheet->setCellValueByColumnAndRow($j, $i, JText::sprintf('COM_PROJECTS_HEAD_PAYMENT_EXP_DESC'));
                        if ($j == 4) $sheet->setCellValueByColumnAndRow($j, $i, JText::sprintf('COM_PROJECTS_HEAD_ITEM_UNIT'));
                        if ($j == 5) $sheet->setCellValueByColumnAndRow($j, $i, JText::sprintf('COM_PROJECTS_HEAD_ITEM_PRICE_RUB_SHORT'));
                        if ($j == 6) $sheet->setCellValueByColumnAndRow($j, $i, JText::sprintf('COM_PROJECTS_HEAD_ITEM_PRICE_USD_SHORT'));
                        if ($j == 7) $sheet->setCellValueByColumnAndRow($j, $i, JText::sprintf('COM_PROJECTS_HEAD_ITEM_PRICE_EUR_SHORT'));
                        if ($j == 8) $sheet->setCellValueByColumnAndRow($j, $i, JText::sprintf('COM_PROJECTS_HEAD_ITEM_PRICE_ITEMS_COUNT_SHORT'));
                        if ($j == 9) $sheet->setCellValueByColumnAndRow($j, $i, JText::sprintf('COM_PROJECTS_HEAD_ITEM_PRICE_RUB'));
                        if ($j == 10) $sheet->setCellValueByColumnAndRow($j, $i, JText::sprintf('COM_PROJECTS_HEAD_ITEM_PRICE_USD'));
                        if ($j == 11) $sheet->setCellValueByColumnAndRow($j, $i, JText::sprintf('COM_PROJECTS_HEAD_ITEM_PRICE_EUR'));
                    }
                    if ($j == 0) $sheet->setCellValueByColumnAndRow($j, $i + 1, $data[$i - 1]['item_title']);
                    if ($j == 1) $sheet->setCellValueByColumnAndRow($j, $i + 1, $data[$i - 1]['contract']);
                    if ($j == 2) $sheet->setCellValueByColumnAndRow($j, $i + 1, $data[$i - 1]['stands']);
                    if ($j == 3) $sheet->setCellValueByColumnAndRow($j, $i + 1, $data[$i - 1]['title']);
                    if ($j == 4) $sheet->setCellValueByColumnAndRow($j, $i + 1, $data[$i - 1]['unit']);
                    if ($j == 5) $sheet->setCellValueByColumnAndRow($j, $i + 1, $data[$i - 1]['price']['rub'] ?? 0);
                    if ($j == 6) $sheet->setCellValueByColumnAndRow($j, $i + 1, $data[$i - 1]['price']['usd'] ?? 0);
                    if ($j == 7) $sheet->setCellValueByColumnAndRow($j, $i + 1, $data[$i - 1]['price']['eur'] ?? 0);
                    if ($j == 8) $sheet->setCellValueByColumnAndRow($j, $i + 1, $data[$i - 1]['value'] ?? 0);
                    if ($j == 9) $sheet->setCellValueByColumnAndRow($j, $i + 1, $data[$i - 1]['amount']['rub'] ?? 0);
                    if ($j == 10) $sheet->setCellValueByColumnAndRow($j, $i + 1, $data[$i - 1]['amount']['usd'] ?? 0);
                    if ($j == 11) $sheet->setCellValueByColumnAndRow($j, $i + 1, $data[$i - 1]['amount']['eur'] ?? 0);
                }
            }
            $sheet->getColumnDimension('A')->setWidth(66);
            $sheet->getColumnDimension('B')->setWidth(21);
            $sheet->getColumnDimension('C')->setWidth(20);
            $sheet->getColumnDimension('D')->setWidth(61);
            $sheet->getColumnDimension('E')->setWidth(9);
            $sheet->getColumnDimension('F')->setWidth(15);
            $sheet->getColumnDimension('G')->setWidth(15);
            $sheet->getColumnDimension('H')->setWidth(15);
            $sheet->getColumnDimension('I')->setWidth(15);
            $sheet->getColumnDimension('J')->setWidth(15);
            $sheet->getColumnDimension('K')->setWidth(15);
            $sheet->getColumnDimension('L')->setWidth(15);
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

        }
        if ($this->itemID == 0) {
            for ($i = 1; $i < count($data) + 1; $i++) {
                for ($j = 0; $j < 10; $j++) {
                    if ($i == 1) {
                        if ($j == 0) $sheet->setCellValueByColumnAndRow($j, $i, JText::sprintf('COM_PROJECTS_HEAD_ITEM_APPLICATION'));
                        if ($j == 1) $sheet->setCellValueByColumnAndRow($j, $i, JText::sprintf('COM_PROJECTS_HEAD_ITEM_TITLE'));
                        if ($j == 2) $sheet->setCellValueByColumnAndRow($j, $i, JText::sprintf('COM_PROJECTS_HEAD_ITEM_UNIT'));
                        if ($j == 3) $sheet->setCellValueByColumnAndRow($j, $i, JText::sprintf('COM_PROJECTS_HEAD_ITEM_PRICE_RUB_SHORT'));
                        if ($j == 4) $sheet->setCellValueByColumnAndRow($j, $i, JText::sprintf('COM_PROJECTS_HEAD_ITEM_PRICE_USD_SHORT'));
                        if ($j == 5) $sheet->setCellValueByColumnAndRow($j, $i, JText::sprintf('COM_PROJECTS_HEAD_ITEM_PRICE_EUR_SHORT'));
                        if ($j == 6) $sheet->setCellValueByColumnAndRow($j, $i, JText::sprintf('COM_PROJECTS_HEAD_ITEM_PRICE_ITEMS_COUNT_SHORT'));
                        if ($j == 7) $sheet->setCellValueByColumnAndRow($j, $i, JText::sprintf('COM_PROJECTS_HEAD_ITEM_PRICE_RUB'));
                        if ($j == 8) $sheet->setCellValueByColumnAndRow($j, $i, JText::sprintf('COM_PROJECTS_HEAD_ITEM_PRICE_USD'));
                        if ($j == 9) $sheet->setCellValueByColumnAndRow($j, $i, JText::sprintf('COM_PROJECTS_HEAD_ITEM_PRICE_EUR'));
                    }
                    if ($j == 0) $sheet->setCellValueByColumnAndRow($j, $i + 1, $data[$i - 1]['application']);
                    if ($j == 1) $sheet->setCellValueByColumnAndRow($j, $i + 1, $data[$i - 1]['title']);
                    if ($j == 2) $sheet->setCellValueByColumnAndRow($j, $i + 1, $data[$i - 1]['unit']);
                    if ($j == 3) $sheet->setCellValueByColumnAndRow($j, $i + 1, $data[$i - 1]['price']['rub'] ?? 0);
                    if ($j == 4) $sheet->setCellValueByColumnAndRow($j, $i + 1, $data[$i - 1]['price']['usd'] ?? 0);
                    if ($j == 5) $sheet->setCellValueByColumnAndRow($j, $i + 1, $data[$i - 1]['price']['eur'] ?? 0);
                    if ($j == 6) $sheet->setCellValueByColumnAndRow($j, $i + 1, $data[$i - 1]['value'] ?? 0);
                    if ($j == 7) $sheet->setCellValueByColumnAndRow($j, $i + 1, $data[$i - 1]['amount']['rub'] ?? 0);
                    if ($j == 8) $sheet->setCellValueByColumnAndRow($j, $i + 1, $data[$i - 1]['amount']['usd'] ?? 0);
                    if ($j == 9) $sheet->setCellValueByColumnAndRow($j, $i + 1, $data[$i - 1]['amount']['eur'] ?? 0);
                }
            }
            $sheet->getColumnDimension('A')->setWidth(20);
            $sheet->getColumnDimension('B')->setWidth(66);
            $sheet->getColumnDimension('C')->setWidth(11);
            $sheet->getColumnDimension('D')->setWidth(15);
            $sheet->getColumnDimension('E')->setWidth(15);
            $sheet->getColumnDimension('F')->setWidth(15);
            $sheet->getColumnDimension('G')->setWidth(11);
            $sheet->getColumnDimension('H')->setWidth(15);
            $sheet->getColumnDimension('I')->setWidth(15);
            $sheet->getColumnDimension('J')->setWidth(15);
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
        }
        $filename = ($this->itemID != 0) ? JFile::makeSafe($this->getExhibitorTitle()) : 'Report';
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

    public function getItemID(): int
    {
        return $this->itemID;
    }

    /* Сортировка по умолчанию */
    protected function populateState($ordering = 'i.title_ru', $direction = 'asc')
    {
        $search = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
        $this->setState('filter.search', $search);
        $project = $this->getUserStateFromRequest($this->context . '.filter.project', 'filter_project');
        $this->setState('filter.project', $project);
        $status = $this->getUserStateFromRequest($this->context . '.filter.status', 'filter_status');
        $this->setState('filter.status', $status);
        $manager = $this->getUserStateFromRequest($this->context . '.filter.manager', 'filter_manager');
        $this->setState('filter.manager', $manager);
        parent::populateState($ordering, $direction);
    }

    protected function getStoreId($id = '')
    {
        $id .= ':' . $this->getState('filter.search');
        $id .= ':' . $this->getState('filter.project');
        $id .= ':' . $this->getState('filter.status');
        $id .= ':' . $this->getState('filter.manager');
        return parent::getStoreId($id);
    }
}