<?php
use Joomla\CMS\MVC\View\HtmlView;

defined('_JEXEC') or die;

class ProjectsViewItems extends HtmlView
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

		// Show the toolbar
		$this->toolbar();

		// Show the sidebar
		$this->helper = new ProjectsHelper();
		$this->helper->addSubmenu('items');
		$this->sidebar = JHtmlSidebar::render();

		// Display it all
		return parent::display($tpl);
	}

	private function toolbar()
	{
		JToolBarHelper::title(JText::sprintf('COM_PROJECTS_MENU_ITEMS'), '');

        if (ProjectsHelper::canDo('projects.access.prices'))
        {
            JToolbarHelper::addNew('item.add');
            JToolbarHelper::editList('item.edit');
            JToolbarHelper::deleteList('', 'items.delete');
        }
        JToolbarHelper::custom('items.standard_columns', '', '', 'COM_PROJECTS_ACTION_SET_STANDARD_COLUMNS');
        JToolbarHelper::custom('items.reset_columns', '', '', 'COM_PROJECTS_ACTION_RESET_COLUMNS');
		if (ProjectsHelper::canDo('core.admin'))
		{
			JToolBarHelper::preferences('com_projects');
		}
	}
}
