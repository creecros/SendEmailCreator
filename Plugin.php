<?php



namespace Kanboard\Plugin\SendEmailCreator;



use Kanboard\Core\Plugin\Base;
use Kanboard\Plugin\SendEmailCreator\Action\SendTaskAssignee;
use Kanboard\Plugin\SendEmailCreator\Action\SendTaskCreator;



class Plugin extends Base

{
    
	public function initialize()
    
	{
        
		$this->actionManager->register(new SendTaskCreator($this->container));
    	        $this->actionManager->register(new SendTaskAssignee($this->container));
	}

	
	public function getPluginName()	
	{ 		 
		return 'Auto Email Action Extender'; 
	}

	public function getPluginAuthor() 
	{ 	 
		return 'Craig Crosby'; 
	}

	public function getPluginVersion() 
	{ 	 
		return '0.0.1'; 
	}

	public function getPluginDescription() 
	{ 
		return 'Add the automatic actions to Send a task by email to the creator or assignee of the task'; 
	}

	public function getPluginHomepage() 
	{ 	 
		return 'https://github.com/creecros/SendTaskCreator'; 
	}
}
