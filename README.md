## Checkout our latest project
[![](https://raw.githubusercontent.com/docpht/docpht/master/public/assets/img/logo.png)](https://github.com/docpht/docpht)

- With [DocPHT](https://github.com/docpht/docpht) you can take notes and quickly document anything and without the use of any database.
-----------
[![Latest release](https://img.shields.io/github/release/creecros/SendEmailCreator.svg)](https://github.com/creecros/SendEmailCreator/releases)
[![GitHub license](https://img.shields.io/github/license/Naereen/StrapDown.js.svg)](https://github.com/creecros/SendEmailCreator/blob/master/LICENSE)
[![Maintenance](https://img.shields.io/badge/Maintained%3F-yes-green.svg)](https://github.com/creecros/SendEmailCreator/graphs/contributors)
[![Open Source Love](https://badges.frapsoft.com/os/v1/open-source.svg?v=103)]()
[![Downloads](https://img.shields.io/github/downloads/creecros/SendEmailCreator/total.svg)](https://github.com/creecros/SendEmailCreator/releases)

Donate to help keep this project maintained.
<a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=SEGNEVQFXHXGW&source=url">
<img src="https://www.paypalobjects.com/en_US/i/btn/btn_donate_SM.gif" border="0" name="submit" title="PayPal - The safer, easier way to pay online!" alt="Donate with PayPal button" /></a>

## :star: If you use it, you should star it on Github! 
- It's the least you can do for all the work put into it!

# Auto Email Extended Actions
This plugin will add 5 new automatic actions to Kanboard.

*1.) Send task by email to assignee*

*2.) Send task by email to creator*

*3.) Send email of an impending due date*

*4.) Send email of an impending subtask due date to assigned user*
* This requires the [Subtask Due Date Plugin](https://github.com/eSkiSo/Subtaskdate) to function.

*5.) Send comment by email.*

## Send Email to Assignee

This Automatic Action will allow you to send by email, a task to the assignee.

## Send Email to Creator

This Automatic Action will allow you to send by email, a task to the creator.

## Send email of an impending due date

this action will send an email of an impending due date to either the task creator, assignee or both. Duration is defined by user, i.e. 1 day would start sending emails of tasks when there is less than 1 day before due date.

## Send email of an impending subtask due date

this action will send an email of an impending due date to the subtask assignee. Duration is defined by user, i.e. 1 day would start sending emails of tasks when there is less than 1 day before due date. Subtask matked as "Done" will be ignored. The "Subject" will be the name of the Subtask, and the "Body" of the Email will be the Task.

## Send comment by email

This action will send a comment by email, when a new comment is created on a task, to assignee, creator or both. The email will contain the comment, and task info.


## Install
Create a directory **SendEmailCreator** under the folder **plugins**
- Copy all source files in this new directory.

