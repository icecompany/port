<?php
defined('_JEXEC') or die;
use Joomla\CMS\MVC\Model\ListModel;

class ProjectsModelPayments extends ListModel
{
    public function __construct(array $config)
    {
        if (empty($config['filter_fields']))
        {
            $config['filter_fields'] = array(
                'pm.pp',
                'pm.dat',
                'score',
                'c.number',
                'length(c.number), c.number',
                'title_ru_short',
                'author',
                'exhibitor',
                'project',
                'manager',
                'search',
                'currency',
                'dat',
                'pm.amount',
            );
        }
        parent::__construct($config);
    }

    protected function _getListQuery()
    {
        $db =& $this->getDbo();
        $query = $db->getQuery(true);
        $query
            ->select("`pm`.`id`, `pm`.`pp`, `pm`.`amount`, `u`.`name` as `author`, DATE_FORMAT(`pm`.`dat`,'%d.%m.%Y') as `dat`")
            ->select("`e`.`title_ru_full`, `e`.`title_ru_short`, `e`.`title_en`, `e`.`id` as `exponentID`")
            ->select("`p`.`title_ru` as `project`, `p`.`id` as `projectID`")
            ->select("`c`.`id` as `contractID`, IFNULL(`c`.`number_free`,`c`.`number`) as `number`, `c`.`currency`")
            ->select("`s`.`number` as `score`, `s`.`id` as `scoreID`, `s`.`amount` as `score_amount`, DATE_FORMAT(`s`.`dat`,'%d.%m.%Y') as `score_dat`")
            ->from("`#__prj_payments` as `pm`")
            ->leftJoin("`#__prj_scores` as `s` ON `s`.`id` = `pm`.`scoreID`")
            ->leftJoin("`#__prj_contracts` as `c` ON `c`.`id` = `s`.`contractID`")
            ->leftJoin("`#__prj_projects` as `p` ON `p`.`id` = `c`.`prjID`")
            ->leftJoin("`#__prj_exp` as `e` ON `e`.`id` = `c`.`expID`")
            ->leftJoin("`#__users` as `u` ON `u`.`id` = `pm`.`created_by`");
        /* Фильтр */
        $search = $this->getState('filter.search');
        if (!empty($search))
        {
            $search = $db->quote('%' . $db->escape($search, true) . '%', false);
            $query->where('(`e`.`title_ru_full` LIKE ' . $search . 'OR `e`.`title_ru_short` LIKE ' . $search . 'OR `e`.`title_en` LIKE ' . $search . 'OR `p`.`title_ru` LIKE ' . $search . ')');
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
        // Фильтруем по счёту.
        $score = $this->getState('filter.score');
        if (is_numeric($score))
        {
            $query->where('`pm`.`scoreID` = ' . (int) $score);
        }
        // Фильтруем по менеджеру.
        $manager = $this->getState('filter.manager');
        if (is_numeric($manager)) {
            $query->where('`c`.`managerID` = ' . (int)$manager);
        }
        // Фильтруем по дате.
        $dat = $this->getState('filter.dat');
        if (!empty($dat))
        {
            $dat = $this->_db->quote($this->_db->escape($dat));
            $query->where('`pm`.`dat` = ' . $dat);
        }
        // Фильтруем по валюте.
        $currency = $this->getState('filter.currency');
        if (!empty($currency))
        {
            $currency = $db->quote('%' . $db->escape($currency, true) . '%', false);
            $query->where("`c`.`currency` LIKE {$currency}");
        }
        // Фильтруем по ID счёта из URL
        $scoreID = JFactory::getApplication()->input->getInt('scoreID', 0);
        if ($scoreID != 0)
        {
            $query->where('`pm`.`scoreID` = ' . (int) $scoreID);
        }

        /* Сортировка */
        $orderCol  = $this->state->get('list.ordering');
        $orderDirn = $this->state->get('list.direction');
        if ($orderCol == 'c.number') {
            if ($orderDirn == 'asc') $orderCol = 'length(c.number), c.number';
            if ($orderDirn == 'desc') $orderCol = 'length(c.number) desc, c.number';
        }
        $query->order($db->escape($orderCol . ' ' . $orderDirn));

        return $query;
    }

    /**
     * Возвращает сумму сделанных платежей по указанному счёту
     * @param int $scoreID
     * @return float
     * @since 1.0.3.9
     */
    public function getScorePayments(int $scoreID): float
    {
        if ($scoreID == 0) return 0;
        $scoreID = $this->_db->escape($scoreID);
        $db =& $this->getDbo();
        $query = $db->getQuery(true);
        $query
            ->select("ROUND(IFNULL(SUM(`amount`),0),2)")
            ->from("`#__prj_payments`")
            ->where("`scoreID` = {$scoreID}");
        return $db->setQuery($query)->loadResult();
    }

    /**
     * Возвращает сумму сделанных платежей по указанному номеру сделки
     * @param int $contractID ID сделки
     * @return float
     * @since 1.0.3.9
     * @deprecated 1.0.4.3
     */
    public function getContractPayments(int $contractID): float
    {
        if ($contractID == 0) return 0;
        $contractID = $this->_db->escape($contractID);
        $db =& $this->getDbo();
        $query = $db->getQuery(true);
        $query
            ->select("IFNULL(SUM(`p`.`amount`),0)")
            ->from("`#__prj_payments` as `p`")
            ->leftJoin("`#__prj_scores` as `s` ON `s`.`id` = `p`.`scoreID`")
            ->leftJoin("`#__prj_contracts` as `c` ON `c`.`id` = `s`.`contractID`")
            ->where("`s`.`contractID` = {$contractID}");
        return $db->setQuery($query)->loadResult();
    }

    public function getItems()
    {
        $items = parent::getItems();
        $result = array('items' => array(), 'amount' => array('rub' => 0, 'usd' => 0, 'eur' => 0));
        $return = base64_encode("index.php?option=com_projects&view=payments");
        $contracts = ListModel::getInstance('Contracts', 'ProjectsModel');
        foreach ($items as $item) {
            if ($item->currency == null) exit(var_dump($item));
            $exponentName = ProjectsHelper::getExpTitle($item->title_ru_short, $item->title_ru_full, $item->title_en);
            $arr = array();
            $arr['id'] = $item->id;
            $url = JRoute::_("index.php?option=com_projects&amp;task=score.edit&amp;id={$item->scoreID}&amp;return={$return}");
            $score = JText::sprintf('COM_PROJECTS_HEAD_SCORE_NUM_AMOUNT_FROM', $item->score, $item->score_amount, $item->currency, $item->score_dat);
            $arr['score'] = (!ProjectsHelper::canDo('core.accountant')) ? $score : JHtml::link($url, $score);
            $url = JRoute::_("index.php?option=com_projects&amp;task=payment.edit&amp;id={$item->id}");
            $arr['pp'] = (!ProjectsHelper::canDo('core.accountant')) ? JText::sprintf('COM_PROJECTS_HEAD_PAYMENT_PP_TITLE', $item->pp) : JHtml::link($url, JText::sprintf('COM_PROJECTS_HEAD_PAYMENT_PP_TITLE', $item->pp));
            $number = (!empty($item->number)) ? JText::sprintf('COM_PROJECTS_HEAD_TODO_DOGOVOR_N', $item->number) : JText::sprintf('COM_PROJECTS_TITLE_CONTRACT_WITHOUT_NUMBER');
            $url = JRoute::_("index.php?option=com_projects&amp;task=contract.edit&amp;id={$item->contractID}&amp;return={$return}");
            $arr['contract'] = JHtml::link($url,$number);
            $arr['dat'] = $item->dat;
            $url = JRoute::_("index.php?option=com_projects&amp;task=exhibitor.edit&amp;id={$item->exponentID}&amp;return={$return}");
            $arr['exp'] = JHtml::link($url, $exponentName);
            $url = JRoute::_("index.php?option=com_projects&amp;task=project.edit&amp;id={$item->projectID}&amp;return={$return}");
            $arr['project'] = (!ProjectsHelper::canDo('core.general')) ? $item->project : JHtml::link($url, $item->project);
            $arr['amount'] = ProjectsHelper::getCurrency((float) $item->amount, $item->currency);
            $arr['author'] = $item->author;
            $arr['stands'] = implode(' ', $contracts->getStandsForContract($item->contractID));
            $result['items'][] = $arr;
            $result['amount'][$item->currency] += $item->amount;
        }
        return $result;
    }

    /* Сортировка по умолчанию */
    protected function populateState($ordering = 'length(c.number), c.number', $direction = 'asc')
    {
        $search = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search', '', 'string');
        $this->setState('filter.search', $search);
        $exhibitor = $this->getUserStateFromRequest($this->context . '.filter.exhibitor', 'filter_exhibitor', '', 'string');
        $this->setState('filter.exhibitor', $exhibitor);
        $project = $this->getUserStateFromRequest($this->context . '.filter.project', 'filter_project', '', 'string');
        $this->setState('filter.project', $project);
        $score = $this->getUserStateFromRequest($this->context . '.filter.score', 'filter_score', '', 'string');
        $this->setState('filter.score', $score);
        $currency = $this->getUserStateFromRequest($this->context . '.filter.currency', 'filter_currency');
        $this->setState('filter.currency', $currency);
        $dat = $this->getUserStateFromRequest($this->context . '.filter.dat', 'filter_dat', '', 'string');
        $this->setState('filter.dat', $dat);
        $manager = $this->getUserStateFromRequest($this->context . '.filter.manager', 'filter_manager');
        $this->setState('filter.manager', $manager);
        parent::populateState($ordering, $direction);
    }

    protected function getStoreId($id = '')
    {
        $id .= ':' . $this->getState('filter.search');
        $id .= ':' . $this->getState('filter.exhibitor');
        $id .= ':' . $this->getState('filter.project');
        $id .= ':' . $this->getState('filter.score');
        $id .= ':' . $this->getState('filter.currency');
        $id .= ':' . $this->getState('filter.dat');
        $id .= ':' . $this->getState('filter.manager');
        return parent::getStoreId($id);
    }
}