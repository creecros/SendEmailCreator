# Auto Email Extended Actions
This plugin will add 4 new automatic actions to Kanboard.

*1.) Send task by email to assignee*

*2.) Send task by email to creator*

*3.) Send email of an impending due date*

*4.) Send email of an impending subtask due date to assigned user*
* This requires the [Subtask Due Date Plugin](https://github.com/eSkiSo/Subtaskdate) to function.

## Send Email to Assignee

This Automatic Action will allow you to send by email, a task to the assignee.

## Send Email to Creator

This Automatic Action will allow you to send by email, a task to the creator.

## Send email of an impending due date

this action will send an email of an impending due date to both creator and assignee. Duration is defined by user, i.e. 1 day would start sending emails of tasks when there is less than 1 day before due date.

## Send email of an impending subtask due date

this action will send an email of an impending due date to the subtask assignee. Duration is defined by user, i.e. 1 day would start sending emails of tasks when there is less than 1 day before due date. Subtask matked as "Done" will be ignored. The "Subject" will be the name of the Subtask, and the "Body" of the Email will be the Task.

## Install
Create a directory **SendEmailCreator** under the folder **plugins**
- Copy all source files in this new directory.

