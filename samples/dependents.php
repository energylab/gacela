<?php
/** 
 * @author noah
 * @date 4/20/11
 * @brief
 * 
 */

require '_init.php';

if(isset($_GET['id']) || isset($_POST['id'])) {
	$id = isset($_GET['id']) ? $_GET['id'] : $_POST['id'];
	$model = \Gacela::instance()->loadMapper('wizard')->find($id);
}

If(count($_POST)) {
	if(!isset($model)) {
		switch($_POST['role']) {
			case 'student':
			case 'teacher':
				$model = ucfirst($_POST['role']);
				break;
			default:
				$model = 'Wizard';
				break;
		}

		$model = "\\App\\Model\\{$model}";

		$model = new $model;
	}

	unset($_POST['id']);
	unset($_POST['save']);
	
	if(!$model->save($_POST)) {
		exit(debug($model->errors));
	}
}

$wizards = \Gacela::instance()->loadMapper('wizard')->findAll();

?>

<h3>Dependency Mapping</h3>

<form method="post" enctype="multipart/form-data" action="<?= $_SERVER['PHP_SELF']?>">
	<input type="hidden" name="id" value="<?= isset($model->wizardId) ? $model->wizardId : null ?>" />
	
	<label>Full Name</label>
	<input type="text" name="fullName" /><br/>

	<label>Role</label>
	<select name="role">
		<option></option>
		<option>student</option>
		<option>teacher</option>
	</select><br/>

	<label>Location</label>
	<input type="text" name="locationName" /><br/>

	<input type="submit" name="save" />
</form>

<table>
	<thead>
	<tr>
		<th>Name</th>
		<th>Class</th>
		<th>Location</th>
	</tr>
	</thead>
	<tbody>

<?
foreach($wizards as $wiz) {
	echo 	'<tr>
				<td>'.$wiz->fullName.'</td>
				<td>'.get_class($wiz).'</td>
				<td>'.$wiz->locationName.'</td>
			</tr>';
}
?>


		</tbody>
	</table>