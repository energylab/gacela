<?php
/** 
 * @author Noah Goodrich
 * @date 10/8/11
 * @brief
 * 
 */
 ?>
 
<p><?= $this->message ?></p>
<form action="/singleInheritance/form/<?= $this->model->wizardId ?>" method="post">

	<label>Full Name</label>
	<input type="text" name="fullName" value="<?= $this->model->fullName ?>" /><br/>

	<input type="submit" />
</form>