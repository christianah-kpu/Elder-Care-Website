<?php 
$page_title = "Self Report";
include '../includes/header.php'; 
?>

<div class="container mt-5">

    <h2 class="mb-4">Self Report</h2>

    <!-- Form -->
    <div class="card shadow p-4 mb-4">

        <form method="POST" action="">

            <div class="mb-3">
                <label class="form-label">Mood</label>
                <select class="form-select" name="mood">
                    <option>Good</option>
                    <option>Okay</option>
                    <option>Bad</option>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Pain Level (1–10)</label>
                <input type="number" class="form-control" name="pain" min="1" max="10">
            </div>

            <div class="mb-3">
                <label class="form-label">Sleep Quality</label>
                <select class="form-select" name="sleep">
                    <option>Good</option>
                    <option>Average</option>
                    <option>Poor</option>
                </select>
            </div>

            <button type="submit" class="btn btn-success w-100">
                Submit Report
            </button>

        </form>

    </div>

    <!-- Existing Reports -->
    <div class="card shadow p-4">
        <h4>My Reports</h4>

        <table class="table table-bordered mt-3">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Mood</th>
                    <th>Pain</th>
                    <th>Sleep</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <!-- Example -->
                <tr>
                    <td>2026-03-30</td>
                    <td>Good</td>
                    <td>2</td>
                    <td>Good</td>
                    <td>
                        <button class="btn btn-warning btn-sm">Edit</button>
                        <button class="btn btn-danger btn-sm">Delete</button>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>


    <!-- BACK -->
    <div class="text-center mt-4">
    <a href="dashboard.php" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Dashboard
    </a>
    </div>
</div>

<?php include '../includes/footer.php'; ?>