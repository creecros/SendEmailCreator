<?php

namespace Kanboard\Plugin\SendEmailCreator\Action;

use Kanboard\Model\TaskModel;
use Kanboard\Action\Base;

/**
 * Email a task notification of impending due date 
 */
class SubTaskEmailDue extends Base
{
    /**
     * Get automatic action description
     *
     * @access public
     * @return string
     */
    public function getDescription()
    {
        return t('Send email notification of impending subtask due date');
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
            // 'subject' => t('Email subject'),
            'duration' => t('Duration in days'),
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
        $subtasks = array();
        (!empty($this->getParam('duration'))) ? $max = $this->getParam('duration') * 86400 : $max = 0;

        foreach ($data['tasks'] as $task) {
          $subtasks = $this->subtaskModel->getAll($task['id']);
            
            foreach ($subtasks as $subtask) {
            $user = $this->userModel->getById($subtask['user_id']);
          
                $duration = $subtask['due_date'] - time();
                if ($subtask['due_date'] > 0) {
                  if ($subtask['status'] < 2) {  
                    if ($duration < $max) {
                      if (! empty($user['email'])) {
                        $results[] = $this->sendEmail($subtask['task_id'], $subtask['title'], $user);
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
    private function sendEmail($task_id, $subject, array $user)
    {
        $task = $this->taskFinderModel->getDetails($task_id);
        $this->emailClient->send(
            $user['email'],
            $user['name'] ?: $user['username'],
            $subject,
            $this->template->render('notification/task_create', array('task' => $task))
        );
        return true;
    }
}
