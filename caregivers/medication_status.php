<?php include '../includes/header.php'; ?>

<div class="container mt-5">

<h2 class="mb-4">Medication Status</h2>

<div class="card shadow p-4">

<table class="table table-bordered">
<thead>
<tr>
<th>Resident</th>
<th>Medicine</th>
<th>Time</th>
<th>Status</th>
</tr>
</thead>
<tbody>
<!-- Example -->
<tr>
<td>John Doe</td>
<td>Paracetamol</td>
<td>10:00 AM</td>
<td>
<select class="form-select">
<option>given</option>
<option>missed</option>
<option>delayed</option>
</select>
</td>
</tr>
</tbody>
</table>

</div>

</div>

<?php include '../includes/footer.php'; ?>