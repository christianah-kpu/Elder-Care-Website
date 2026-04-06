<?php include '../includes/header.php'; ?>

<div class="container mt-5">

    <h2 class="mb-4">My Health Records</h2>

    <!-- Search -->
    <form class="mb-3">
        <input type="date" class="form-control">
    </form>

    <!-- Health Table -->
    <div class="card shadow p-4">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Blood Pressure</th>
                    <th>Blood Sugar</th>
                    <th>Temperature</th>
                    <th>Heart Rate</th>
                    <th>Notes</th>
                </tr>
            </thead>
            <tbody>
                <!-- Example -->
                <tr>
                    <td>2026-03-30</td>
                    <td>120/80</td>
                    <td>95</td>
                    <td>36.8°C</td>
                    <td>72</td>
                    <td>Normal</td>
                </tr>
            </tbody>
        </table>
    </div>

</div>

<?php include '../includes/footer.php'; ?>