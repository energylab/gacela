<?php
/** 
 * @author Noah Goodrich
 * @date 9/9/11
 * @brief
 * 
 */
 ?>
 
<form action="/crud/form/<?= $this->house->houseId ?>" method="post" enctype="multipart/form-data">
	<label for="house">House Name</label>
	<input type="hidden" name='id' value="<?= $this->house->houseId ?>" />
	<input type="text" name="house" value="<?= $this->house->houseName ?>"/>
	<input type="submit" name="addHouse" />
</form>
