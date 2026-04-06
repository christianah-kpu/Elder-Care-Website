<?php include '../includes/header.php'; ?>

<div class="container mt-5">

    <h2 class="mb-4">Generate Reports</h2>

    <!-- Filter Section -->
    <div class="card shadow p-4 mb-4">
        <h4>Select Report Criteria</h4>

        <form method="GET" action="">

            <div class="row">

                <!-- Report Type -->
                <div class="col-md-4">
                    <label class="form-label">Report Type</label>
                    <select name="report_type" class="form-select">
                        <option value="monthly">Monthly</option>
                        <option value="yearly">Yearly</option>
                    </select>
                </div>

                <!-- Year -->
                <div class="col-md-4">
                    <label class="form-label">Year</label>
                    <input type="number" name="year" class="form-control" placeholder="e.g. 2026">
                </div>

                <!-- Month -->
                <div class="col-md-4">
                    <label class="form-label">Month</label>
                    <select name="month" class="form-select">
                        <option value="">-- Select Month --</option>
                        <option value="1">January</option>
                        <option value="2">February</option>
                        <option value="3">March</option>
                        <option value="4">April</option>
                        <option value="5">May</option>
                        <option value="6">June</option>
                        <option value="7">July</option>
                        <option value="8">August</option>
                        <option value="9">September</option>
                        <option value="10">October</option>
                        <option value="11">November</option>
                        <option value="12">December</option>
                    </select>
                </div>

            </div>

            <button type="submit" class="btn btn-primary mt-3">
                Generate Report
            </button>

        </form>
    </div>

    <!-- User & Medication Summary -->
    <div class="card shadow p-4 mb-4">
        <h4>User & Medication Summary</h4>

        <table class="table table-bordered mt-3">
            <thead>
                <tr>
                    <th>Resident</th>
                    <th>Missed Medications</th>
                </tr>
            </thead>
            <tbody>
                <!-- Example -->
                <tr>
                    <td>John Doe</td>
                    <td>5</td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Health Irregularities -->
    <div class="card shadow p-4">
        <h4>Health Irregularities Summary</h4>

        <table class="table table-bordered mt-3">
            <thead>
                <tr>
                    <th>Resident</th>
                    <th>Abnormal BP</th>
                    <th>Abnormal Sugar</th>
                    <th>High Temp</th>
                    <th>Irregular HR</th>
                </tr>
            </thead>
            <tbody>
                <!-- Example -->
                <tr>
                    <td>Jane Doe</td>
                    <td>3</td>
                    <td>2</td>
                    <td>1</td>
                    <td>4</td>
                </tr>
            </tbody>
        </table>
    </div>

</div>

<?php include '../includes/footer.php'; ?>