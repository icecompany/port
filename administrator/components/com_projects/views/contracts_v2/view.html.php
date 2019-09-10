<?php
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView;

defined('_JEXEC') or die;

class ProjectsViewContracts_v2 extends HtmlView
{
	protected $helper;
	protected $sidebar = '';
	public $items, $pagination, $uid, $state, $links, $filterForm, $activeFilters, $userSettings;

	public function display($tpl = null)
	{
	    $this->items = $this->get('Items');
	    $this->pagination = $this->get('Pagination');
	    $this->state = $this->get('State');
        $this->filterForm = $this->get('FilterForm');
        $this->activeFilters = $this->get('ActiveFilters');
        $this->userSettings = ProjectsHelper::getUserSettings();
        $this->removeFilters();
        $this->filterForm->setValue('limit', 'list', $this->state->get('list.limit'));

        // Show the toolbar
		$this->toolbar();

		// Show the sidebar
		$this->helper = new ProjectsHelper();
		$this->helper->addSubmenu('contracts_v2');
		$this->sidebar = JHtmlSidebar::render();

		// Display it all
		return parent::display($tpl);
	}

	private function toolbar()
	{
	    //$bar = JToolbar::getInstance('toolbar');

		JToolBarHelper::title(Text::_('COM_PROJECTS_MENU_CONTRACTS_V2'), '');

        if (ProjectsHelper::canDo('projects.access.contracts.standart'))
        {
            JToolbarHelper::addNew('contract.add');
        }
        if (ProjectsHelper::canDo('projects.access.contracts.standart'))
        {
            JToolbarHelper::editList('contract.edit');
        }
        if (ProjectsHelper::canDo('projects.access.contracts.full'))
        {
            JToolbarHelper::deleteList('COM_PROJECT_QUEST_REMOVE_CONTRACT', 'contracts.delete');
        }
        if (ProjectsHelper::canDo('projects.access.contracts.columns')) {
            JToolbarHelper::custom('contracts.setcolumn1', '', '', 'COM_PROJECTS_ACTION_CONTRACT_SET_COLUMN_1');
            JToolbarHelper::custom('contracts.setcolumn2', '', '', 'COM_PROJECTS_ACTION_CONTRACT_SET_COLUMN_2');
            JToolbarHelper::custom('contracts.setcolumn3', '', '', 'COM_PROJECTS_ACTION_CONTRACT_SET_COLUMN_3');
        }

        /*
            $layout = new JLayoutFile('joomla.toolbar.batch');
            $batchButtonHtml = $layout->render(array('title' => 'Settings'));
            $bar->appendButton('Custom', $batchButtonHtml, 'batch', $listSelect = false);
         */
        if (ProjectsHelper::canDo('projects.access.contracts.standart'))
        {
            JToolbarHelper::custom('contracts.getNumber', '', '', 'COM_PROJECTS_ACTION_CONTRACT_SET_NUMBER');
        }

        JToolbarHelper::custom('settings.contracts_v2', 'options', '', 'COM_PROJECTS_MENU_SETTING_VISIBILITY', false);

        if (ProjectsHelper::canDo('core.admin'))
		{
			JToolBarHelper::preferences('com_projects');
		}
	}

	private function removeFilters()
    {
        if (!$this->userSettings['contracts_v2-filter_doc_status']) $this->filterForm->removeField('doc_status', 'filter');
        if (!$this->userSettings['contracts_v2-filter_currency']) $this->filterForm->removeField('currency', 'filter');
        if (!$this->userSettings['contracts_v2-filter_manager']) $this->filterForm->removeField('manager', 'filter');
        if (!$this->userSettings['contracts_v2-filter_activity']) $this->filterForm->removeField('activity', 'filter');
        if (!$this->userSettings['contracts_v2-filter_rubric']) $this->filterForm->removeField('rubric', 'filter');
        if (!$this->userSettings['contracts_v2-filter_status']) $this->filterForm->removeField('status', 'filter');
    }
}
