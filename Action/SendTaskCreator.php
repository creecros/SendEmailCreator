<?php

namespace Kanboard\Plugin\SendEmailCreator\Action;

use Kanboard\Model\TaskModel;
use Kanboard\Action\Base;

class SendTaskCreator extends Base
{
   
    public function getDescription()
    {
        return t('Send a task by email to creator');
    }

   
    public function getCompatibleEvents()
    {
        return array(
            TaskModel::EVENT_MOVE_COLUMN,
            TaskModel::EVENT_CLOSE,
            TaskModel::EVENT_CREATE,
            TaskModel::EVENT_UPDATE,
            TaskModel::EVENT_ASSIGNEE_CHANGE,
	);
    }

   
    public function getActionRequiredParameters()
    {
        return array(
            'column_id' => t('Column'),
	        'subject' => t('Email subject'),
	        'check_box_include_title' => t('Include Task Title and ID in subject line?'),
        );
    }

   
    public function getEventRequiredParameters()
    {
        return array(
            'task_id',
            'task' => array(
                'project_id',
                'column_id',
                'title',
		        'creator_id',
            ),
        );
    }

    
    public function doAction(array $data)
    {
        $user = $this->userModel->getById($data['task']['creator_id']);
        if ($this->getParam('check_box_include_title') == true ){
            $subject = $this->getParam('subject') . ": " . $data['task']['title'] . "(#" . $data['task_id'] . ")";
        } else {
            $subject = $this->getParam('subject');
        }
        
        if (! empty($user['email'])) {
            $this->emailClient->send(
                $user['email'],
                $user['name'] ?: $user['username'],
                $subject,
                $this->template->render('notification/task_create', array(
                    'task' => $data['task'],
                ))
            );

            return true;
        }

        return false;
    }


   
    public function hasRequiredCondition(array $data)
    {
        return $data['task']['column_id'] == $this->getParam('column_id');
    }
}
