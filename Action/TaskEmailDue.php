<?php
namespace Kanboard\Plugin\SendEmailCreator\Action;

use Kanboard\Model\TaskModel;
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
        return array(
        'tasks',
        'task' => array(
                'project_id',
                'column_id',
            	'owner_id',
        );
        
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
        $t_assignee = $this->userModel->getById($data['task']['owner_id']);
        $t_creator = $this->userModel->getById($data['task']['creator_id']);
        if (! empty($t_assignee['email'])) {
            foreach ($data['tasks'] as $task) {
                $duration = $task['due_date'] - time();
                if ($duration < $max) {
                    $results[] = $this->sendEmail($task['id'], $t_assignee);
                }
            }
        }
        if (! empty($t_creator['email'])) {
            foreach ($data['tasks'] as $task) {
                $duration = $task['due_date'] - time();
                if ($duration < $max) {
                    $results[] = $this->sendEmail($task['id'], $t_creator);
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
