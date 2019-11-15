<?php
defined('_JEXEC') or die;
use Joomla\CMS\MVC\Model\ListModel;

class ProjectsModelBuilding extends ListModel
{
    public function __construct(array $config)
    {
        if (empty($config['filter_fields']))
        {
            $config['filter_fields'] = array(
                'title_ru_short',
                'contract',
                'freeze',
                'search',
                'project',
                'sq',
                'manager',
                'standtype',
                'standstate',
                'standstatus',
                's.status',
                's.arrival', 'arrival',
                's.department', 'department',
                'hotel',
                'exp_status',
                'exhibitor',
                'stand',
                'nc.title_ru',
                'h.title_ru',
            );
        }

        $this->return = ProjectsHelper::getReturnUrl();
        $this->userSettings = ProjectsHelper::getUserSettings();

        parent::__construct($config);
    }

    /**
     * Возвращает название подключаемого слоя
     * @return string название слоя
     * @since 1.1.3.6
     */
    public function getLayout(): string
    {
        $layout = '';
        $project = $this->state->get('filter.project');
        if (empty($project)) $project = ProjectsHelper::getActiveProject();
        if (is_numeric($project)) {
            $tip = ProjectsHelper::getProjectType($project);
            switch ($tip)
            {
                case 0: {
                    $layout = '';
                    break;
                }
                case 1: {
                    $layout = 'rooms';
                    break;
                }
                default: $layout = '';
            }
        }
        return $layout;
    }

    protected function _getListQuery()
    {
        $layout = $this->getLayout();
        $db =& $this->getDbo();
        $query = $db->getQuery(true);
        $query
            ->select('`s`.`id` as `standID`, IFNULL(`cat`.`number`,`cat`.`title`) as `stand`, `s`.`freeze`, IFNULL(`s`.`tip`,0) as `tip`, IFNULL(`s`.`status`,1) as `status`, `cat`.`square`as `sq`')
            ->select("`e`.`title_ru_full`, `e`.`title_ru_short`, `e`.`title_en`, `c`.`expID` as `exponentID`")
            ->select("IFNULL(`c`.`number_free`,`c`.`number`) as `contract`, `c`.`id` as `contractID`, `c`.`status` as `exp_status`")
            ->select("`u`.`name` as `manager`")
            ->select("(SELECT getStandPavilion(`cat`.`number`)) as `pavilion`")
            ->from("`#__prj_catalog` as `cat`")
            ->leftJoin("`#__prj_stands` as `s` ON `s`.`catalogID` = `cat`.`id`")
            ->leftJoin("`#__prj_contracts` as `c` ON `c`.`id` = `s`.`contractID`")
            ->leftJoin("`#__prj_exp` as `e` ON `e`.`id` = `c`.`expID`")
            ->leftJoin("`#__users` as `u` ON `u`.`id` = `c`.`managerID`");
        if ($this->userSettings['building-show_city'] || $this->userSettings['building-show_city_fact']) {
            $query
                ->select("r.name as reg, rf.name as reg_fact")
                ->leftJoin("`#__grph_cities` r on r.id = e.regID")
                ->leftJoin("`#__grph_cities` rf on rf.id = e.regID_fact");
        }
        if ($layout != '') {
            $query->where("`e`.`id` IS NOT NULL");
            $query
                ->select("DATE_FORMAT(`s`.`arrival`,'%d.%m.%Y') as `arrival`, DATE_FORMAT(`s`.`department`,'%d.%m.%Y') as `department`")
                ->select("`nc`.`title_ru` as `number_category`, `h`.`title_ru` as `hotel`")
                ->leftJoin("`#__prj_hotels_number_categories` as `nc` ON `nc`.`id` = `cat`.`categoryID`")
                ->leftJoin("`#__prj_hotels` as `h` ON `h`.`id` = `nc`.`hotelID`");
        }
        /* Фильтр */
        $search = $this->getState('filter.search');
        if (!empty($search)) {
            $search = $db->quote('%' . $db->escape($search, true) . '%', false);
            $query->where('`cat`.`number` LIKE ' . $search);
        }
        // Фильтруем по экспоненту.
        $exhibitor = $this->getState('filter.exhibitor');
        if (is_numeric($exhibitor)) {
            $query->where('`c`.`expID` = ' . (int) $exhibitor);
        }
        // Фильтруем по отелю.
        $hotel = $this->getState('filter.hotel');
        if (is_numeric($hotel)) {
            $query->where('`h`.`id` = ' . (int) $hotel);
        }
        // Фильтруем по датам заезда и выезда
        $arrival = $this->getState('filter.arrival');
        $department = $this->getState('filter.department');
        if (!empty($arrival) && !empty($department)) {
            $d = JDate::getInstance($arrival);
            $arrival = $db->q($d->format("Y-m-d"));
            $d = JDate::getInstance($department);
            $department = $db->q($d->format("Y-m-d"));
            $query->where("(`s`.`arrival` >= {$arrival} AND `s`.`department` <= {$department})");
        }
        // Фильтруем по проекту.
        $project = $this->getState('filter.project');
        if (empty($project)) $project = ProjectsHelper::getActiveProject();
        if (is_numeric($project)) {
            $catalog = ProjectsHelper::getProjectCatalog($project);
            $query->where('`cat`.`titleID` = ' . (int)$catalog);
        }
        // Фильтруем по менеджеру.
        $manager = $this->getState('filter.manager');
        if (is_numeric($manager)) {
            $query->where('`c`.`managerID` = ' . (int)$manager);
        }
        // Фильтруем по типу стенда.
        $standtype = $this->getState('filter.standtype');
        if (is_numeric($standtype)) {
            $query->where('`s`.`tip` = ' . (int)$standtype);
        }
        // Фильтруем по текущему состоянию стенда стенда.
        $standstate = $this->getState('filter.standstate');
        if (is_numeric($standstate)) {
            $query->where('`s`.`status` = ' . (int)$standstate);
        }
        // Фильтруем по статусу стенда.
        $standstatus = $this->getState('filter.standstatus');
        if (is_numeric($standstatus)) {
            if ($standstatus == 0) $query->where('`e`.`id` IS NULL');
            if ($standstatus == 1) $query->where('`e`.`id` IS NOT NULL');
        }

        /* Сортировка */
        $orderCol  = $this->state->get('list.ordering', 'pavilion, stand');
        $orderDirn = $this->state->get('list.direction', 'asc');
        $query->order($db->escape($orderCol . ' ' . $orderDirn));

        $this->setState('list.limit', 0);

        return $query;
    }

    public function getItems($raw = false)
    {
        $layout = $this->getLayout();
        $items = parent::getItems();
        $results = array();
        $stands = array(); //Массив контракт - массив стендов
        $ids = array();
        foreach ($items as $item) {
            if ($item->standID == null) continue;
            $ids[] = $item->standID;
            $url = JRoute::_("index.php?option=com_projects&amp;task=exhibitor.edit&amp;id={$item->exponentID}&amp;return={$this->return}");
            $exhibitor = ($item->exponentID != null) ? ProjectsHelper::getExpTitle($item->title_ru_short, $item->title_ru_full, $item->title_en) : '';
            $link = JHtml::link($url, $exhibitor);
            $arr['exhibitor'] = (!$item->sq == null) ?  $link : JText::sprintf('COM_PROJECTS_HEAD_CONTRACT_STAND_FREE');
            if ($layout != '') $arr['exhibitor'] = $link;
            $url = JRoute::_("index.php?option=com_projects&amp;task=stand.edit&amp;contractID={$item->contractID}&amp;id={$item->standID}&amp;return={$this->return}");
            $arr['stand'] = (!$item->contractID == null) ? JHtml::link($url, $item->stand) : $item->stand;
            $title = sprintf("%s (%s, %s)", $item->stand, ProjectsHelper::getStandType($item->tip), ProjectsHelper::getStandStatus($item->status));
            $stands[$item->contractID][] = JHtml::link($url, $title);
            $number = (!empty($item->contract)) ? JText::sprintf('COM_PROJECTS_HEAD_TODO_DOGOVOR_N', $item->contract) : JText::sprintf('COM_PROJECTS_TITLE_CONTRACT_WITHOUT_NUMBER');
            $url = JRoute::_("index.php?option=com_projects&amp;task=contract.edit&amp;id={$item->contractID}&amp;return={$this->return}");
            $arr['contract'] = (!$item->exponentID == null) ? JHtml::link($url,$number) : JText::sprintf('COM_PROJECTS_HEAD_CONTRACT_STAND_FREE');
            $arr['sq'] = (!$item->sq == null) ? sprintf("%s %s", $item->sq , JText::sprintf('COM_PROJECTS_HEAD_ITEM_UNIT_SQM')) : JText::sprintf('COM_PROJECTS_HEAD_CONTRACT_STAND_FREE');
            $arr['contractID'] = (!$item->exponentID == null) ? $item->contractID : JText::sprintf('COM_PROJECTS_HEAD_CONTRACT_STAND_FREE');
            $arr['manager'] = (!$item->exponentID == null) ? $item->manager : JText::sprintf('COM_PROJECTS_HEAD_CONTRACT_STAND_FREE');
            $arr['status'] = (!$item->exponentID == null) ? JText::sprintf('COM_PROJECTS_HEAD_CONTRACT_STAND_BUSY') : JText::sprintf('COM_PROJECTS_HEAD_CONTRACT_STAND_FREE_OK');
            $arr['exp_status'] = (!$item->exponentID == null) ? ProjectsHelper::getExpStatus($item->exp_status) : JText::sprintf('COM_PROJECTS_HEAD_CONTRACT_STAND_FREE');
            $arr['freeze'] = $item->freeze ?? JText::sprintf('COM_PROJECTS_HEAD_CONTRACT_STAND_FREE');
            $arr['tip'] = (!$item->exponentID == null) ? ProjectsHelper::getStandType($item->tip) : JText::sprintf('COM_PROJECTS_HEAD_CONTRACT_STAND_FREE');
            if (ProjectsHelper::canDo('core.general')) {
                $arr['scheme'] = (!$item->exponentID == null) ? ProjectsHelper::getStandStatus($item->status) : JText::sprintf('COM_PROJECTS_HEAD_CONTRACT_STAND_FREE');
            }
            $arr['pavilion'] = $item->pavilion;
            if (!isset($arr['square'][$item->pavilion])) $arr['square'][$item->pavilion] = 0;
            $arr['square'][$item->pavilion] += $item->sq;
            if ($layout == 'rooms') {
                $arr['number_category'] = $item->number_category;
                $arr['hotel'] = $item->hotel;
                $arr['arrival'] = $item->arrival;
                $arr['department'] = $item->department;
            }
            $arr['standID'] = $item->standID;
            if ($this->userSettings['building-show_city_fact']) $arr['city_fact'] = $item->reg_fact;
            if ($this->userSettings['building-show_city']) $arr['city'] = $item->reg;
            $results['stands'][$item->standID] = $arr;
        }
        $results['advanced'] = $this->getAdvanced($ids);
        ksort($results['advanced']);
        return $results;
    }

    /**
     * Возвращает информацию о дополнительных услугах на стендах
     * @param array $ids массив с ID стендов
     * @return array
     * @since 1.2.3.3
     */
    public function getAdvanced(array $ids = array()): array
    {
        if (empty($ids)) return array();
        $ids = implode(', ', $ids);
        $db =& $this->getDbo();
        $query = $db->getQuery(true);
        $query
            ->select("`sa`.`standID`, `sa`.`itemID`, SUM(`sa`.`value`) as `value`")
            ->select("`i`.`title_ru` as `item`")
            ->from("`#__prj_stands_advanced` as `sa`")
            ->leftJoin("`#__prc_items` as `i` on `i`.`id` = `sa`.`itemID`")
            ->where("`sa`.`standID` IN ({$ids})")
            ->group("`sa`.`standID`, `sa`.`itemID`");
        $items = $db->setQuery($query)->loadObjectList();
        $result = array();
        if (empty($items)) return array();
        foreach ($items as $item) {
            if(!isset($result[$item->item])) $result[$item->item] = array();
            $result[$item->item][$item->standID] = $item->value;
        }
        return $result;
    }

    /* Сортировка по умолчанию */
    protected function populateState($ordering = null, $direction = null)
    {
        $search = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
        $this->setState('filter.search', $search);
        $exhibitor = $this->getUserStateFromRequest($this->context . '.filter.exhibitor', 'filter_exhibitor');
        $this->setState('filter.exhibitor', $exhibitor);
        $project = $this->getUserStateFromRequest($this->context . '.filter.project', 'filter_project');
        $this->setState('filter.project', $project);
        $manager = $this->getUserStateFromRequest($this->context . '.filter.manager', 'filter_manager');
        $this->setState('filter.manager', $manager);
        $standtype = $this->getUserStateFromRequest($this->context . '.filter.standtype', 'filter_standtype');
        $this->setState('filter.standtype', $standtype);
        $standstate = $this->getUserStateFromRequest($this->context . '.filter.standstate', 'filter_standstate');
        $this->setState('filter.standstate', $standstate);
        $standstatus = $this->getUserStateFromRequest($this->context . '.filter.standstatus', 'filter_standstatus');
        $this->setState('filter.standstatus', $standstatus);
        $arrival = $this->getUserStateFromRequest($this->context . '.filter.arrival', 'filter_arrival');
        $this->setState('filter.arrival', $arrival);
        $department = $this->getUserStateFromRequest($this->context . '.filter.department', 'filter_department');
        $this->setState('filter.department', $department);
        $hotel = $this->getUserStateFromRequest($this->context . '.filter.hotel', 'filter_hotel');
        $this->setState('filter.hotel', $hotel);
        parent::populateState('pavilion, stand', 'asc');
    }

    protected function getStoreId($id = '')
    {
        $id .= ':' . $this->getState('filter.search');
        $id .= ':' . $this->getState('filter.exhibitor');
        $id .= ':' . $this->getState('filter.project');
        $id .= ':' . $this->getState('filter.manager');
        $id .= ':' . $this->getState('filter.standtype');
        $id .= ':' . $this->getState('filter.standstatus');
        $id .= ':' . $this->getState('filter.arrival');
        $id .= ':' . $this->getState('filter.department');
        $id .= ':' . $this->getState('filter.hotel');
        return parent::getStoreId($id);
    }

    private $return, $userSettings;
}