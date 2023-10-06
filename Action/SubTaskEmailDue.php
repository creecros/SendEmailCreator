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
            'column_id' => t('Choose a column to ignore, when a task is in this column, no emails will be sent.')
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

    public function isTimeToSendEmail($project, $task, $subtask)
    {
        // Change $verbose to true while debugging
        $verbose = false;
        $verbose_prefix = $verbose ? "isTimeToSendEmail() - Task \"{$project['name']}::{$task['title']}({$task['id']})\" " : "";

        // Don't send if the subtask doesn't have a due date
        if ($subtask['due_date'] == 0) {
            $verbose && print "\n{$verbose_prefix}doesn't have a due date; Not time to send.";

            return false;
        }

        // Don't send if the subtask itself isn't due soon enough
        (!empty($this->getParam('duration'))) ? $max_duration = $this->getParam('duration') * 86400 : $max_duration = 0;
        $duration = $subtask['due_date'] - time();
        if ($duration >= $max_duration) {
            $verbose && print "\n{$verbose_prefix}isn't due soon enough ($duration v. $max_duration); Not time to send.";

            return false;
        }

        // Don't send if we've already sent too recently
        $minimum_email_span = 86400;
        $last_emailed = $this->taskMetadataModel->get($task['id'], 'task_last_emailed_toassignee' . $subtask['id'], time() - 86400);
        $last_email_span = time() - $last_emailed;
        if ($last_email_span < $minimum_email_span) {
            $verbose && print "\n{$verbose_prefix}has already been emailed about too recently ($last_email_span v. $minimum_email_span); Not time to send.";

            return false;
        }

        //
        $verbose && print "\n{$verbose_prefix}Sending email!";

        return true;
    }


    public function doAction(array $data)
    {
        $results = array();
        $subtasks = array();

        foreach ($data['tasks'] as $task) {
            $subtasks = $this->subtaskModel->getAll($task['id']);
            $project = $this->projectModel->getById($task['project_id']);

            // Only email for active projects
            if ($project['is_active'] && $task['column_id'] != $this->getParam('column_id')) {
                foreach ($subtasks as $subtask) {
                    $user = $this->userModel->getById($subtask['user_id']);

                    $duration = $subtask['due_date'] - time();
                    if ($subtask['due_date'] > 0) {
                        if ($subtask['status'] < 2) {
                            $is_time_to_send = $this->isTimeToSendEmail($project, $task, $subtask);
                            if ($is_time_to_send) {
                                if (! empty($user['email'])) {
                                    $results[] = $this->sendEmail($subtask['task_id'], $subtask['title'], $user);
                                    $this->taskMetadataModel->save($task['id'], ['task_last_emailed_toassignee' . $subtask['id'] => time()]);
                                }
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
