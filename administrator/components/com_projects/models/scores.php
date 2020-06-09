<?php
defined('_JEXEC') or die;
use Joomla\CMS\MVC\Model\ListModel;

class ProjectsModelScores extends ListModel
{
    public function __construct(array $config)
    {
        if (empty($config['filter_fields']))
        {
            $config['filter_fields'] = array(
                's.dat',
                'number',
                'number_contract',
                'length(number_contract), number_contract',
                'project',
                'title_ru_short',
                'amount',
                'currency',
                'state',
                'manager',
                'search',
                'exhibitor',
                'payments',
                'debt',
                's.state',
            );
        }
        parent::__construct($config);
    }

    protected function _getListQuery()
    {
        $db =& $this->getDbo();
        $query = $db->getQuery(true);
        $query
            ->select("`s`.`id`, `s`.`contractID`, DATE_FORMAT(`s`.`dat`,'%d.%m.%Y') as `dat`, `s`.`number`, `s`.`amount`, `s`.`state`")
            ->select("`e`.`title_ru_short`, `e`.`title_ru_full`, `e`.`title_en`, `e`.`id` as `expID`")
            ->select("`c`.`currency`, IFNULL(`c`.`number_free`,`c`.`number`) as `number_contract`")
            ->select("IFNULL(`p`.`title_ru`,`p`.`title_en`) as `project`, `p`.`id` as `projectID`")
            ->select("IFNULL(`pay`.`payments`,0) as `payments`, `s`.`amount`-IFNULL(`pay`.`payments`,0) as `debt`")
            ->from("`#__prj_scores` as `s`")
            ->leftJoin("`#__prj_score_payments` as `pay` ON `pay`.`scoreID` = `s`.`id`")
            ->leftJoin("`#__prj_contracts` as `c` ON `c`.`id` = `s`.`contractID`")
            ->leftJoin("`#__prj_projects` as `p` ON `p`.`id` = `c`.`prjID`")
            ->leftJoin("`#__prj_exp` as `e` ON `e`.`id` = `c`.`expID`");
        if (!ProjectsHelper::canDo('core.accountant') && !ProjectsHelper::canDo('core.general'))
        {
            $userID = JFactory::getUser()->id;
            $query->where("`c`.`managerID` = {$userID}");
        }
        /* Фильтр */
        $search = $this->getState('filter.search');
        if (!empty($search))
        {
            $search = $db->quote('%' . $db->escape($search, true) . '%', false);
            $query->where('(`e`.`title_ru_full` LIKE ' . $search . 'OR `e`.`title_ru_short` LIKE ' . $search . 'OR `e`.`title_en` LIKE ' . $search . ')');
        }
        // Фильтруем по состоянию оплаты.
        $published = $this->getState('filter.state');
        if (is_numeric($published))
        {
            $query->where('`s`.`state` = ' . (int) $published);
        }
        elseif ($published === '')
        {
            $query->where('(`s`.`state` = 0 OR `s`.`state` = 1)');
        }
        // Фильтруем по менеджеру.
        $manager = $this->getState('filter.manager');
        if (is_numeric($manager)) {
            $query->where('`c`.`managerID` = ' . (int)$manager);
        }
        // Фильтруем по валюте.
        $currency = $this->getState('filter.currency');
        if (!empty($currency))
        {
            $currency = $db->quote('%' . $db->escape($currency, true) . '%', false);
            $query->where("`c`.`currency` LIKE {$currency}");
        }
        // Фильтруем по сделке.
        $contract = $this->getState('filter.contract');
        if (is_numeric($contract))
        {
            $query->where('`s`.`contractID` = ' . (int) $contract);
        }
        // Фильтруем по экспоненту.
        $exhibitor = $this->getState('filter.exhibitor');
        if (is_numeric($exhibitor))
        {
            $query->where('`c`.`expID` = ' . (int) $exhibitor);
        }
        // Фильтруем по проекту.
        $project = $this->getState('filter.project');
        if (empty($project)) $project = ProjectsHelper::getActiveProject();
        if (is_numeric($project))
        {
            $query->where('`c`.`prjID` = ' . (int) $project);
        }

        /* Сортировка */
        $orderCol  = $this->state->get('list.ordering');
        $orderDirn = $this->state->get('list.direction');

        if ($orderCol == 'number_contract') {
            if ($orderDirn == 'asc') $orderCol = 'length(number_contract), number_contract';
            if ($orderDirn == 'desc') $orderCol = 'length(number_contract) desc, number_contract';
        }

        $query->order($db->escape($orderCol . ' ' . $orderDirn));

        return $query;
    }

    public function getItems()
    {
        $items = parent::getItems();
        $result = array('items' => array(), 'amount' => array('rub' => 0, 'usd' => 0, 'eur' => 0), 'debt' => array('rub' => 0, 'usd' => 0, 'eur' => 0));
        $return = base64_encode("index.php?option=com_projects&view=scores");
        foreach ($items as $item)
        {
            $arr = array();
            $arr['id'] = $item->id;
            $arr['contract_id'] = $item->contractID;
            $arr['number'] = JHtml::link(JRoute::_("index.php?option=com_projects&amp;task=score.edit&amp;id={$item->id}"), "№".$item->number);
            $arr['dat'] = $item->dat;
            $number = (!empty($item->number_contract)) ? JText::sprintf('COM_PROJECTS_HEAD_TODO_DOGOVOR_N', $item->number_contract) : JText::sprintf('COM_PROJECTS_TITLE_CONTRACT_WITHOUT_NUMBER');
            $url = JRoute::_("index.php?option=com_projects&amp;task=contract.edit&amp;id={$item->contractID}&amp;return={$return}");
            $arr['contract'] = JHtml::link($url,$number);
            $arr['showPaymens'] = JHtml::link(JRoute::_("index.php?option=com_projects&amp;view=payments&amp;scoreID={$item->id}"), JText::sprintf('COM_PROJECTS_MENU_PAYMENTS'));
            $arr['doPayment'] = (!ProjectsHelper::canDo('core.general') && !ProjectsHelper::canDo('core.accountant')) ? JText::sprintf('COM_PROJECTS_ACTION_TODO_PAYMENT') : JHtml::link(JRoute::_("index.php?option=com_projects&amp;task=payment.add&amp;scoreID={$item->id}&amp;return={$return}"), JText::sprintf('COM_PROJECTS_ACTION_TODO_PAYMENT'));
            $exp = ProjectsHelper::getExpTitle($item->title_ru_short, $item->title_ru_full, $item->title_en);
            $arr['exp'] = JHtml::link(JRoute::_("index.php?option=com_projects&amp;task=exhibitor.edit&amp;id={$item->expID}&amp;return={$return}"), $exp);
            $arr['project'] = (!ProjectsHelper::canDo('core.general')) ? $item->project : JHtml::link(JRoute::_("index.php?option=com_projects&amp;task=project.edit&amp;id={$item->projectID}&amp;return={$return}"), $item->project);
            $arr['amount'] = ProjectsHelper::getCurrency((float) ($item->amount), $item->currency);
            $arr['state'] = $item->state;
            $arr['payments'] = ProjectsHelper::getCurrency((float) $item->payments, $item->currency);
            $arr['debt'] = ProjectsHelper::getCurrency((float) $item->debt, $item->currency);
            $arr['state_text'] = ProjectsHelper::getScoreState($item->state);
            if (($item->debt > 0) && ($item->debt < $item->amount)) $arr['state_text'] = JText::sprintf('COM_PROJECTS_HEAD_SCORE_STATE_2');
            if ($item->debt < 0) $arr['state_text'] = JText::sprintf('COM_PROJECTS_HEAD_SCORE_STATE_3');
            $arr['color'] = ($arr['debt'] != 0) ? 'red' : 'green';
            $result['items'][] = $arr;
            $result['amount'][$item->currency] += $item->amount;
            $result['payments'][$item->currency] += $item->payments;
            $result['debt'][$item->currency] += ($item->debt < 0) ? 0 : $item->debt;
        }
        return $result;
    }

    /* Сортировка по умолчанию */
    protected function populateState($ordering = 'length(number_contract), number_contract', $direction = 'asc')
    {
        $published = $this->getUserStateFromRequest($this->context . '.filter.state', 'filter_state', '', 'string');
        $this->setState('filter.state', $published);
        $contract = $this->getUserStateFromRequest($this->context . '.filter.contract', 'filter_contract', '', 'string');
        $this->setState('filter.contract', $contract);
        $exhibitor = $this->getUserStateFromRequest($this->context . '.filter.exhibitor', 'filter_exhibitor', '', 'string');
        $this->setState('filter.exhibitor', $exhibitor);
        $project = $this->getUserStateFromRequest($this->context . '.filter.project', 'filter_project', '', 'string');
        $this->setState('filter.project', $project);
        $currency = $this->getUserStateFromRequest($this->context . '.filter.currency', 'filter_currency');
        $this->setState('filter.currency', $currency);
        $manager = $this->getUserStateFromRequest($this->context . '.filter.manager', 'filter_manager');
        $this->setState('filter.manager', $manager);
        parent::populateState($ordering, $direction);
    }

    protected function getStoreId($id = '')
    {
        $id .= ':' . $this->getState('filter.state');
        $id .= ':' . $this->getState('filter.contract');
        $id .= ':' . $this->getState('filter.exhibitor');
        $id .= ':' . $this->getState('filter.project');
        $id .= ':' . $this->getState('filter.currency');
        $id .= ':' . $this->getState('filter.manager');
        return parent::getStoreId($id);
    }
}