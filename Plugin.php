<?php



namespace Kanboard\Plugin\SendEmailCreator;



use Kanboard\Core\Plugin\Base;

use Kanboard\Plugin\SendEmailCreator\Action\SendTaskCreator;



class Plugin extends Base

{
    
	public function initialize()
    
	{
        
		$this->actionManager->register(new SendTaskCreator($this->container));
    
	}

	
	public function getPluginName()	
	{ 		 
		return 'Auto Email Creator'; 
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
		return 'Send a task by email to the creator of the task'; 
	}

	public function getPluginHomepage() 
	{ 	 
		return 'https://github.com/creecros/SendTaskCreator'; 
	}
}
