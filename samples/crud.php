<?php
/** 
 * @author Noah Goodrich
 * @date 5/2/11
 * @brief
 * 
 */

require '_init.php';

if(isset($_GET['id']) || isset($_POST['id'])) {
	$id = isset($_GET['id']) ? $_GET['id'] : $_POST['id'];
	
	$model = Gacela::instance()->loadMapper('house')->find($id);
}

if(isset($_GET['action']) && $_GET['action'] == 'delete') {
	$model->delete();
}

if(count($_POST)) {
	if(!isset($model)) {
		$model = new \App\Model\House;
	}

	$model->houseName = $_POST['house'];

	if(!$model->save()) {
		$error = 'House Name is not valid';
	} else {
		unset($model);
	}
}

$houses = \Gacela::instance()->loadMapper('house')->findAll();
?>

<h1>Creating, Updating, Deleting a Model</h1>

<?= isset($error) ? '<h3>'.$error.'</h3>' : null ?>
<form action="<?= $_SERVER['PHP_SELF'] ?>" method="post" enctype="multipart/form-data">
	<label for="house">House Name</label>
	<input type="hidden" name='id' value="<?= isset($model) ? $model->houseId : null ?>" />
	<input type="text" name="house" value="<?= isset($model) ? $model->houseName : null ?>"/>
	<input type="submit" name="addHouse" />
</form>

<table>
	<thead>
	<tr>
		<th>House Name</th>
		<th>Options</th>
	</tr>
	</thead>
	<tbody>
<? foreach($houses as $house): ?>
	<tr>
		<td><?= $house->houseName ?></td>
		<td>
			<a href="crud.php?action=edit&id=<?= $house->houseId ?>">Edit</a>&nbsp;
			<a href="crud.php?action=delete&id=<?= $house->houseId ?>">Delete</a>&nbsp;
		</td>
	</tr>
<? endforeach ?>
	</tbody>
</table>
 
