<?php
/** 
 * @author Noah Goodrich
 * @date 5/11/11
 * @brief
 * 
 */

require '_init.php';

$errors = '';

if(isset($_GET['id']) || isset($_POST['id'])) {
	$id = isset($_GET['id']) ? $_GET['id'] : $_POST['id'];
	$model = Gacela::instance()->loadMapper('teacher')->find($id);
} else {
	$model = new \App\Model\Teacher;
	$model->role = 'teacher';
}

if(count($_POST)) {
	$model->fullName = $_POST['fullName'];

	if(!$model->save()) {
		foreach($model->errors as $key => $val) {
			$errors .= 'Field: '.$key.' ErrorCode: '.$val.'<br/>';
		}
	}
}

?>
 
<h3>Single Table Inheritance</h3>

<p><?//= $errors ?></p>
<form action="/single-table-inheritance.php" method="post">
	<input type="hidden" name="id" value="<?= $model->wizardId ?>" />

	<label>Full Name</label>
	<input type="text" name="fullName" value="<?= $model->fullName ?>" />

	<input type="submit" />
</form>

<table>
	<thead>
		<th>Full Name</th>
		<th>Courses</th>
		<th>Model</th>
		<th>Options</th>
	</thead>
	<tbody>
<?
$criteria = new \Gacela\Criteria;

// For this list we only want to see teachers
$criteria->notEquals('role', 'student');

$wizards = Gacela::instance()->loadMapper('wizard')->findAll($criteria);

foreach($wizards as $wiz) {
	$names = array();

	if(isset($wiz->courses)) {
		foreach($wiz->courses as $course) {
			$names[] = $course->subject;
		}
	}

	$names = join('<br/>', $names);

	echo 	'<tr>
				<td>'.$wiz->fullName.'</td>
				<td>'.$names.'</td>
				<td>'.get_class($wiz).'</td>
				<td>
					<a href="/single-table-inheritance.php?id='.$wiz->wizardId.'">Edit</a>
					<a href="/single-table-inheritance.php?id='.$wiz->wizardId.'">Delete</a>
				</td>
			</tr>';
}
?>

	</tbody>
</table>