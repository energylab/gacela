<?php
/** 
 * @author Noah Goodrich
 * @date 5/2/11
 * @brief
 * 
 */

require '_init.php';

if(isset($_GET['id'])) {
	$model = Gacela::instance()->loadMapper('house')->find($_GET['id']);

	if($_GET['action'] == 'edit') {
		$house = $model->houseName;
		$id = $model->houseId;
	}
}

if(count($_POST)) {
	if(!isset($model)) {
		$model = new \App\Model\House;
	}

	$model->houseName = $_POST['house'];

	if(!$model->save()) {
		$house = $_POST['house'];
		$error = 'House Name is not valid';
	}
}

$houses = \Gacela::instance()->loadMapper('house')->findAll();
?>

<h1>Creating, Updating, Deleting a Model</h1>

<?= isset($error) ? '<h3>'.$error.'</h3>' : null ?>
<form action="<?= $_SERVER['PHP_SELF'] ?>" method="post" enctype="multipart/form-data">
	<label for="house">House Name</label>
	<input type="hidden" name='houseId' value="<?= isset($id) ? $id : null ?>" />
	<input type="text" name="house" value="<?= isset($house) ? $house : null ?>"/>
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
 
