<?php

namespace Kanboard\Plugin\SendEmailCreator\Action;

use Kanboard\Model\TaskModel;
use Kanboard\Model\TaskMetadataModel;
use Kanboard\Action\Base;

/**
 * Email a task notification of impending due date 
 */
class TaskEmailDue extends Base
{
    /**
     * Get automatic action description
     *
     * @access public
     * @return string
     */
    public function getDescription()
    {
        return t('Send email notification of impending due date');
    }
    /**
     * Get the list of compatible events
     *
     * @access public
     * @return array
     */
    public function getCompatibleEvents()
    {
        return array(
            TaskModel::EVENT_DAILY_CRONJOB,
        );
    }
    /**
     * Get the required parameter for the action (defined by the user)
     *
     * @access public
     * @return array
     */
    public function getActionRequiredParameters()
    {
        return array(
            'subject' => t('Email subject'),
            'duration' => t('Duration in days'),
            'send_to' => array('assignee' => t('Send to Assignee'), 'creator' => t('Send to Creator'), 'both' => t('Send to Both')),
        );
    }
    /**
     * Get the required parameter for the event
     *
     * @access public
     * @return string[]
     */
    public function getEventRequiredParameters()
    {
        return array('tasks');
        
    }
    /**
     * Check if the event data meet the action condition
     *
     * @access public
     * @param  array   $data   Event data dictionary
     * @return bool
     */
    public function hasRequiredCondition(array $data)
    {
        return count($data['tasks']) > 0;
    }

    public function doAction(array $data)
    {
        $results = array();
        $max = $this->getParam('duration') * 86400;
        
        
        if ($this->getParam('send_to') !== null) { $send_to = $this->getParam('send_to'); } else { $send_to = 'both'; }
        
        if ($send_to == 'assignee' || $send_to == 'both') {
        foreach ($data['tasks'] as $task) {
            $last_emailed = $this->taskMetadataModel->get($task['id'], 'task_last_emailed_toassignee', time() - 86400);
            $last_email_span = time() - $last_emailed;
            if ($last_email_span >= 86400) { $send_email = true; } else { $send_email = false; }
            
            $user = $this->userModel->getById($task['owner_id']);
          
                $duration = $task['date_due'] - time();
                if ($task['date_due'] > 0) {
                  if ($duration < $max) {
                      if (! empty($user['email'])) {
                          if ($send_email) {
                            $results[] = $this->sendEmail($task['id'], $user);
                            $this->taskMetadataModel->save($task['id'], ['task_last_emailed_toassignee' => time()]);
                          }
                      }
                  }
                }
           
        }
        }
        
        if ($send_to == 'creator' || $send_to == 'both') {
        foreach ($data['tasks'] as $task) {
            $last_emailed = $this->taskMetadataModel->get($task['id'], 'task_last_emailed_tocreator', time() - 86400);
            $last_email_span = time() - $last_emailed;
            if ($last_email_span >= 86400) { $send_email = true; } else { $send_email = false; }
            
            $user = $this->userModel->getById($task['creator_id']);
           
                $duration = $task['date_due'] - time();
                if ($task['date_due'] > 0) {
                  if ($duration < $max) {
                        if (! empty($user['email'])) {
                          if ($send_email) {
                            $results[] = $this->sendEmail($task['id'], $user);
                            $this->taskMetadataModel->save($task['id'], ['task_last_emailed_tocreator' => time()]);
                          }
                        }
                                                        
                  }
                }
           
        }
        }
        
        return in_array(true, $results, true);
    }
    /**
     * Send email
     *
     * @access private
     * @param  integer $task_id
     * @param  array   $user
     * @return boolean
     */
    private function sendEmail($task_id, array $user)
    {
        $task = $this->taskFinderModel->getDetails($task_id);
        $this->emailClient->send(
            $user['email'],
            $user['name'] ?: $user['username'],
            $this->getParam('subject'),
            $this->template->render('notification/task_create', array('task' => $task))
        );
        return true;
    }
}
