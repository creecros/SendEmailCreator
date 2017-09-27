<?php

namespace Kanboard\Action;

use Kanboard\Model\TaskModel;

class EmailCreator extends Base
{
   
    public function getDescription()
    {
        return t('Send a task by email to someone');
    }

   
    public function getCompatibleEvents()
    {
        return array(
            TaskModel::EVENT_MOVE_COLUMN,
            TaskModel::EVENT_CLOSE,
            TaskModel::EVENT_CREATE,
        );
    }

   
    public function getActionRequiredParameters()
    {
        return array(
            'column_id' => t('Column'),
	    'user_id' => 0,
            'subject' => t('Email subject'),
        );
    }

   
    public function getEventRequiredParameters()
    {
        return array(
            'task_id',
            'task' => array(
                'project_id',
                'column_id',
		'creator_id',
            ),
        );
    }

    
    public function doAction(array $data)
    {
        $user = $this->userModel->getById($data['task']['creator_id']);

               if (! empty($user['email'])) {
            $this->emailClient->send(
                $user['email'],
                $user['name'] ?: $user['username'],
                $this->getParam('subject'),
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
