<?php

namespace Kanboard\Plugin\SendEmailCreator;

use Kanboard\Core\Plugin\Base;
use Kanboard\Model\CommentModel;
use Kanboard\Plugin\SendEmailCreator\Action\SendTaskAssignee;
use Kanboard\Plugin\SendEmailCreator\Action\SendTaskCreator;
use Kanboard\Plugin\SendEmailCreator\Action\SendTaskComment;
use Kanboard\Plugin\SendEmailCreator\Action\TaskEmailDue;
use Kanboard\Plugin\SendEmailCreator\Action\SubTaskEmailDue;
use Kanboard\Core\Translator;

class Plugin extends Base

{
    
	public function initialize()
    
	{
        
		$this->actionManager->register(new SendTaskCreator($this->container));
		$this->actionManager->register(new SendTaskAssignee($this->container));
		$this->actionManager->register(new TaskEmailDue($this->container));
		$this->actionManager->register(new SubTaskEmailDue($this->container));
		$this->actionManager->register(new SendTaskComment($this->container));
		
		$this->eventManager->register(CommentModel::EVENT_CREATE, 'On comment creation');

	}
	
	public function onStartup()
        {
               Translator::load($this->languageModel->getCurrentLanguage(), __DIR__.'/Locale');
        }

	
	public function getPluginName()	
	{ 		 
		return 'Auto Email Extended Actions'; 
	}

	public function getPluginAuthor() 
	{ 	 
		return 'Craig Crosby'; 
	}

	public function getPluginVersion() 
	{ 	 
		return '1.2.4'; 
	}

	public function getPluginDescription() 
	{ 
		return 'Add the automatic actions to Send a task by email to the creator or assignee of the task. Also, included is an action to send a notification via email to user, creator, and assignee when a due date is impending within a duration.'; 
	}

	public function getPluginHomepage() 
	{ 	 
		return 'https://github.com/creecros/SendTaskCreator'; 
	}
}
