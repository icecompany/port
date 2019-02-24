<?php
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView;

defined('_JEXEC') or die;

class ProjectsViewContracts extends HtmlView
{
	protected $helper;
	protected $sidebar = '';
	public $items, $pagination, $uid, $state, $links, $filterForm, $activeFilters;

	public function display($tpl = null)
	{
	    $this->items = $this->get('Items');
	    $this->pagination = $this->get('Pagination');
	    $this->state = $this->get('State');
        $this->filterForm = $this->get('FilterForm');
        $this->activeFilters = $this->get('ActiveFilters');
        if (!is_numeric($this->state->get('filter.rubric'))) {
            $this->filterForm->removeField('manager', 'filter');
        }
        $this->filterForm->removeField('activity', 'filter');

        // Show the toolbar
		$this->toolbar();

		// Show the sidebar
		$this->helper = new ProjectsHelper();
		$this->helper->addSubmenu('contracts');
		$this->sidebar = JHtmlSidebar::render();

		// Display it all
		return parent::display($tpl);
	}

	private function toolbar()
	{
		JToolBarHelper::title(Text::_('COM_PROJECTS_MENU_CONTRACTS'), '');

        if (ProjectsHelper::canDo('projects.access.contracts.standart'))
        {
            JToolbarHelper::addNew('contract.add');
        }
        if (ProjectsHelper::canDo('projects.access.contracts.standart'))
        {
            JToolbarHelper::editList('contract.edit');
        }
        if (ProjectsHelper::canDo('projects.access.contracts.standart'))
        {
            JToolbarHelper::deleteList('COM_PROJECT_QUEST_REMOVE_CONTRACT', 'contracts.delete');
        }
        JToolbarHelper::divider();
        if (ProjectsHelper::canDo('projects.access.contracts.standart'))
        {
            JToolbarHelper::custom('contracts.getNumber', '', '', 'COM_PROJECTS_ACTION_CONTRACT_SET_NUMBER');
        }
		if (ProjectsHelper::canDo('core.admin'))
		{
			JToolBarHelper::preferences('com_projects');
		}
	}
}
