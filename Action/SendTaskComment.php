<?php

namespace Kanboard\Plugin\SendEmailCreator\Action;

use Kanboard\Model\TaskModel;
use Kanboard\Model\CommentModel;
use Kanboard\Action\Base;

class SendTaskComment extends Base
{
   
    public function getDescription()
    {
        return t('Send new comments on a task by email');
    }

   
    public function getCompatibleEvents()
    {
        return array(
            CommentModel::EVENT_CREATE,
	);
    }

   
    public function getActionRequiredParameters()
    {
        return array(
	        'subject' => t('Email subject'),
	        'send_to' => array('assignee' => t('Send to Assignee'), 'creator' => t('Send to Creator'), 'both' => t('Send to Both')),
	        'check_box_include_title' => t('Include Task Title and ID in subject line?'),
        );
    }

   
    public function getEventRequiredParameters()
    {
        return array(
            'comment',
            'task',
        );
    }

    
    public function doAction(array $data)
    {
        $commentSent = FALSE;
        if ($this->getParam('check_box_include_title') == true ){
            $subject = $this->getParam('subject') . ": " . $data['task']['title'] . "(#" . $data['task']['id'] . ")";
        } else {
            $subject = $this->getParam('subject');
        }
        
        if ($this->getParam('send_to') !== null) { $send_to = $this->getParam('send_to'); } else { $send_to = 'both'; }
        
            if ($send_to == 'assignee' || $send_to == 'both') {
                $user = $this->userModel->getById($data['task']['owner_id']);
    
                if (! empty($user['email'])) {
                    $this->emailClient->send(
                        $user['email'],
                        $user['name'] ?: $user['username'],
                        $subject,
                        $this->template->render('notification/comment_create', array(
                            'task' => $data['task'],
                            'comment' => $data['comment'],
                        ))
                    );
                    $commentSent = TRUE;
                } 
            }
            if ($send_to == 'creator' || $send_to == 'both') {
                $user = $this->userModel->getById($data['task']['creator_id']);
    
                if (! empty($user['email'])) {
                    $this->emailClient->send(
                        $user['email'],
                        $user['name'] ?: $user['username'],
                        $subject,
                        $this->template->render('notification/comment_create', array(
                            'task' => $data['task'],
                            'comment' => $data['comment'],
                        ))
                    );
                    $commentSent = TRUE;
                } 
            }
        return $commentSent;
    }


   
    public function hasRequiredCondition(array $data)
    {
        return true;
    }
}
