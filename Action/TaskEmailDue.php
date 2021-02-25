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

    public function makeDefaultSubject($task)
    {
        $project = $this->projectModel->getById($task['project_id']);

        $remaining = $task['date_due'] - time();
        $days_to_due = 0;
        $seconds_to_due = 0;
        $hours_to_due = 0;

        if ( $remaining > 0 ) {
            $days_to_due = floor($remaining / 86400);
            $seconds_to_due = $remaining % 86400;
            $hours_to_due = $seconds_to_due > 0 ? floor( $seconds_to_due / 3600  ) : 0;
        }

        $time_part = '';
        if ( $days_to_due > 0 ) {
            $time_part .= $days_to_due . ' day' . ($days_to_due == 1 ? '' : 's');
            if ( $days_to_due < 2 && $hours_to_due > 0 ) {
                $time_part .= ' and ' . $hours_to_due . ' hour' . ($hours_to_due == 1 ? '' : 's');
            }
        }
        else if ( $hours_to_due > 0 ) {
            $time_part = $hours_to_due . ' hour' . ($hours_to_due == 1 ? '' : 's');
        }

        $subject = '[' . $project['name']  . '][' . $task['title']  . '] ' . ($time_part ? 'Due in ' . $time_part : 'Task is due');
        //print "\n".$subject."\n";

        return $subject;
    }
    
    public function isTimeToSendEmail($project, $task)
    {
    	// Change $verbose to true while debugging
    	$verbose = false;
    	$verbose_prefix = $verbose ? "isTimeToSendEmail() - Task \"{$project['name']}::{$task['title']}({$task['id']})\" " : "";
    	
        // Don't send if the task doesn't have a due date
        if ($task['date_due'] == 0) {
            
            $verbose && print "\n{$verbose_prefix}doesn't have a due date; Not time to send.";
            
            return false;
        }
        
        // Don't send if the task itself isn't due soon enough
        (!empty($this->getParam('duration'))) ? $max_duration = $this->getParam('duration') * 86400 : $max_duration = 0;
        $duration = $task['date_due'] - time();
        if ($duration >= $max_duration) {
            
            $verbose && print "\n{$verbose_prefix}isn't due soon enough ($duration v. $max_duration); Not time to send.";
            
            return false;
        }
        
        // Don't send if we've already sent too recently
        $minimum_email_span = 86400;
        $last_emailed = $this->taskMetadataModel->get($task['id'], 'task_last_emailed_toassignee', time() - 86400);
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
        
        if ($this->getParam('send_to') !== null) { $send_to = $this->getParam('send_to'); } else { $send_to = 'both'; }
        
        if ($send_to == 'assignee' || $send_to == 'both') {
            
            foreach ($data['tasks'] as $task) {
                
                $project = $this->projectModel->getById($task['project_id']);
                        
                // Only email for active projects
                if ( $project['is_active'] ) {
                    
                    // Decide if it's time to send an email
                    $is_time_to_send = $this->isTimeToSendEmail($project, $task);
                    if ($is_time_to_send) {
                        
                        $user = $this->userModel->getById($task['owner_id']);
                        if (! empty($user['email'])) {
                            $results[] = $this->sendEmail($task['id'], $user);
                            $this->taskMetadataModel->save($task['id'], ['task_last_emailed_toassignee' => time()]);
                        }
                    }
                }
            }
        }
        
        if ($send_to == 'creator' || $send_to == 'both') {
            
            foreach ($data['tasks'] as $task) {
            
                // Only email for active projects
                if ( $project['is_active'] ) {
                    
                    // Only email is enough time has passed since the last one was sent
                    $is_time_to_send = $this->isTimeToSendEmail($project, $task);
                    if ( $is_time_to_send ) {
                        
                        $user = $this->userModel->getById($task['creator_id']);
                        if (! empty($user['email'])) {
                            $results[] = $this->sendEmail($task['id'], $user);
                            $this->taskMetadataModel->save($task['id'], ['task_last_emailed_tocreator' => time()]);
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
        $subject = $this->getParam('subject') ?: $this->makeDefaultSubject($task);
        
        $this->emailClient->send(
            $user['email'],
            $user['name'] ?: $user['username'],
            $subject,
            $this->template->render('notification/task_create', array('task' => $task))
        );
        return true;
    }
}
