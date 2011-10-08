<?php
/** 
 * @author Noah Goodrich
 * @date 10/8/11
 * @brief
 * 
 */
 ?>
 
<p><?= $this->message ?></p>
<form action="/singleInheritance" method="post">
	<input type="hidden" name="id" value="<?= $model->wizardId ?>" />

	<label>Full Name</label>
	<input type="text" name="fullName" value="<?= $model->fullName ?>" />

	<input type="submit" />
</form>