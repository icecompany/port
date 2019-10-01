<?php
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView;

defined('_JEXEC') or die;

class ProjectsViewManagerstat extends HtmlView
{
	protected $helper;
	protected $sidebar = '';
	public $items, $pagination, $uid, $state, $links, $filterForm, $activeFilters, $dat;

	public function display($tpl = null)
	{
	    $this->items = $this->get('Items');
	    $this->pagination = $this->get('Pagination');
	    $this->state = $this->get('State');
        $this->filterForm = $this->get('FilterForm');
        $this->activeFilters = $this->get('ActiveFilters');
        $this->dat = $this->get('Dat');

        // Show the toolbar
		$this->toolbar();

		$this->prepare();

		// Show the sidebar
		$this->helper = new ProjectsHelper();
		$this->helper->addSubmenu('managerstat');
		$this->sidebar = JHtmlSidebar::render();

		// Display it all
		return parent::display($tpl);
	}

	private function toolbar()
	{
		JToolBarHelper::title(Text::_('COM_PROJECTS_MENU_MANAGER_STAT'), '');

        if (ProjectsHelper::canDo('core.admin'))
		{
			JToolBarHelper::preferences('com_projects');
		}
	}

	private function prepare()
    {
        $this->filterForm->setValue('limit', 'list', 0);
        $this->filterForm->setValue('dat', 'filter', $this->state->get('filter.dat'));
        $this->filterForm->setValue('dynamic', 'filter', $this->state->get('filter.dynamic'));
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
