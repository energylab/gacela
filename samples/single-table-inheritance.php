<?php
/** 
 * @author Noah Goodrich
 * @date 5/11/11
 * @brief
 * 
 */

 require '_init.php';

$wizardId = null;
$fullName = null;
$errors = null;

if(count($_POST)) {

	$model = new \App\Model\Teacher;

	$model->fullName = $_POST['fullName'];
	$model->role = 'teacher';

	if(!$model->save()) {
		$errors = join('<br/>', $model->errors);
	}
}
?>
 
<h3>Single Table Inheritance</h3>

<p><?= $errors ?></p>
<form action="/single-table-inheritance.php" method="post">
	<input type="hidden" name="wizardId" value="<? $wizardId ?>" />

	<label>Full Name</label>
	<input type="text" name="fullName" value="<?= $fullName ?>" />

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
					<a href="">Edit</a>
					<a href="">Delete</a>
				</td>
			</tr>';
}
?>

	</tbody>
</table>