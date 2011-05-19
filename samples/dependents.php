<?php
/** 
 * @author noah
 * @date 4/20/11
 * @brief
 * 
 */

require '_init.php';

?>
<h3>Dependency Mapping</h3>

<form>
	<input type="">
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

// Return all wizards who have an address
$wizards = \Gacela::instance()->loadMapper('wizard')->findAll();

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