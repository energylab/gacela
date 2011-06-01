<?php
/** 
 * @author Noah Goodrich
 * @date 4/23/11
 * @brief
 * 
 */

require '_init.php';

$errors = '';
$message = '';

$houses = Gacela::instance()->loadMapper('house')->findAll();

if(isset($_GET['id']) || isset($_POST['id'])) {
	$id = isset($_GET['id']) ? $_GET['id'] : $_POST['id'];
	$student = Gacela::instance()->loadMapper('student')->find($id);
}

if(isset($_GET['action']) && $_GET['action'] == 'delete') {
	$student->delete();
	$message = 'Student deleted<br/>';
}

if(count($_POST)) {
	if(!isset($student)) {
		$student = new \App\Model\Student;
	}

	$student->fullName = $_POST['fullName'];
	$student->role = 'student';
	$student->houseId = $_POST['houseId'];

	if(!$student->save()) {
		exit(debug($student->errors));
	} else {
		$message = 'Student saved<br/>';
	}
}

if(!isset($_GET['action']) || $_GET['action'] != 'edit') {
	unset($student);
}

?>
<h3>Concrete Table Inheritance</h3>

<?= $message ?>
<?= $errors ?>
<form action="/concrete-inheritance.php" method="post">
	<input type="hidden" name="id" value="<?= isset($student) ? $student->wizardId : null ?>" />
	<label>Student Name</label>
	<input type="text" name="fullName"  value="<?= isset($student) ? $student->fullName : null ?>" /><br/>

	<label>House</label>
	<select name="houseId">
	<? foreach($houses as $house): ?>
		<option value="<?= $house->houseId ?>"><?= $house->houseName ?></option>
	<? endforeach; ?>
	</select><br/>

	<input type="submit" />
</form>

<table>
	<thead>
		<th>Student</th>
		<th>House</th>
		<th>Options</th>
	</thead>
	<tbody>
<?
$students = Gacela::instance()->loadMapper('student')->findAll();

foreach($students as $student) {
	echo '<tr>
			<td>'.$student->fullName.'</td>
			<td>'.$student->house->houseName.'</td>
			<td>
				<a href="concrete-inheritance.php?action=edit&id='.$student->wizardId.'">Edit</a>
				<a href="concrete-inheritance.php?action=delete&id='.$student->wizardId.'">Delete</a>
			</td>
		</tr>';
}
?>
	</tbody>
</table>

