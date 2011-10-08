<?php
/** 
 * @author Noah Goodrich
 * @date 9/9/11
 * @brief
 * 
 */
 ?>

<a href="/crud/form">Add House</a>

<table>
	<thead>
	<tr>
		<th>House Name</th>
		<th>Options</th>
	</tr>
	</thead>
	<tbody>
<? foreach($this->houses as $house): ?>
	<tr>
		<td><?= $house->houseName ?></td>
		<td>
			<a href="crud/form/<?= $house->houseId ?>">Edit</a>&nbsp;
			<a href="crud/delete/<?= $house->houseId ?>">Delete</a>&nbsp;
		</td>
	</tr>
<? endforeach ?>
	</tbody>
</table>