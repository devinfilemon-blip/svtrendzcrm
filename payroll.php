<?php
include 'layouts/session.php';
include 'layouts/head-main.php';
include 'layouts/config.php';
include 'layouts/crm-access.php';
crmRequireAdmin($link);
?>
<head>
    <title>Payroll</title>
    <?php include 'layouts/head.php'; ?>
    <?php include 'layouts/head-style.php'; ?>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        .success-message { color: green; font-weight: bold; }
        .error-message { color: red; font-weight: bold; }
        .btn-primary { color: #fff; background-color: #005aa5; border-color: #005aa5; }
        .hrm-form .form-label {
            text-transform: uppercase;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: .3px;
            color: #495057;
        }
        .hrm-section-title { font-weight: 700; font-size: 15px; margin-bottom: 12px; }
        .hrm-section-title.earnings { color: #34c38f; }
        .hrm-section-title.deductions { color: #f46a6a; }
        .hrm-summary-box { background: #f8f9fa; border-radius: 6px; padding: 16px; margin-top: 8px; }
        .hrm-summary-row { display: flex; justify-content: space-between; padding: 6px 0; }
        .hrm-summary-row.net { border-top: 1px solid #dee2e6; margin-top: 6px; padding-top: 12px; font-weight: 700; }
        .hrm-summary-row .val-earn { color: #34c38f; font-weight: 600; }
        .hrm-summary-row .val-ded { color: #f46a6a; font-weight: 600; }
        .hrm-summary-row .val-net { color: #34c38f; font-weight: 700; }
        .badge-paid { background-color: #34c38f; }
        .badge-pending { background-color: #f1b44c; }
    </style>
</head>
<?php include 'layouts/body.php'; ?>
<div id="layout-wrapper">
<?php include 'layouts/menu.php'; ?>
    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12">
                        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                            <h4 class="mb-sm-0 font-size-18">Payroll</h4>
                            <div class="page-title-right">
                                <ol class="breadcrumb m-0">
                                    <li class="breadcrumb-item"><a href="javascript: void(0);">HRM</a></li>
                                    <li class="breadcrumb-item active">Payroll</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-12 d-flex flex-wrap gap-2 justify-content-end align-items-center">
                        <input type="month" class="form-control form-control-sm" id="payrollMonth" style="width: 160px;" value="<?php echo date('Y-m'); ?>">
                        <button type="button" class="btn btn-success btn-sm" onclick="runPayroll()"><i class="bx bx-play-circle"></i> Payroll</button>
                        <a href="list-payroll.php" class="btn btn-info btn-sm">Payroll List</a>
                        <button type="button" class="btn btn-warning btn-sm" onclick="openAddAdvance()"><i class="bx bx-user-plus"></i> Add Advance</button>
                        <button type="button" class="btn btn-primary btn-sm" onclick="openAddStructure()">+ Add Salary Structure</button>
                    </div>
                </div>

                <div><span id="message"></span></div>

                <div class="row">
                    <div class="col-lg-5">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title mb-3">Advances</h5>
                                <div class="table-responsive">
                                    <table id="advanceTable" class="table table-bordered table-striped">
                                        <thead>
                                            <tr>
                                                <th>Employee</th>
                                                <th>Type</th>
                                                <th>Outstanding</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-7">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title mb-3">Salary Structure</h5>
                                <div class="table-responsive">
                                    <table id="datatable" class="table table-bordered table-striped">
                                        <thead>
                                            <tr>
                                                <th>Sr No.</th>
                                                <th>Employee</th>
                                                <th>Effective From</th>
                                                <th>Gross Salary</th>
                                                <th>Deductions</th>
                                                <th>Net Salary</th>
                                                <th>Edit</th>
                                                <th>Delete</th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php include 'layouts/footer.php'; ?>
    </div>
</div>

<!-- Salary Structure Modal -->
<div class="modal fade" id="structureModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Salary Structure</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div><span id="modalMessage"></span></div>
                <form id="structureForm" class="hrm-form" onsubmit="return false;">
                    <input type="hidden" id="structure_id" value="">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label" for="employee_id">Employee</label>
                                <select class="form-select" id="employee_id"></select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label" for="effective_from">Effective From</label>
                                <input type="date" class="form-control" id="effective_from" value="<?php echo date('Y-m-d'); ?>">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="hrm-section-title earnings"><i class="bx bx-plus-circle"></i> Earnings</div>
                            <div class="mb-3">
                                <label class="form-label" for="basic_salary">Basic Salary</label>
                                <input type="number" class="form-control calc-input" id="basic_salary" value="0" min="0" step="0.01">
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="hra">HRA (House Rent)</label>
                                <input type="number" class="form-control calc-input" id="hra" value="0" min="0" step="0.01">
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="da">DA (Dearness)</label>
                                <input type="number" class="form-control calc-input" id="da" value="0" min="0" step="0.01">
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="travel_allowance">Travel Allowance</label>
                                <input type="number" class="form-control calc-input" id="travel_allowance" value="0" min="0" step="0.01">
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="medical_allowance">Medical Allowance</label>
                                <input type="number" class="form-control calc-input" id="medical_allowance" value="0" min="0" step="0.01">
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="special_allowance">Special Allowance</label>
                                <input type="number" class="form-control calc-input" id="special_allowance" value="0" min="0" step="0.01">
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="other_allowance">Other Allowance</label>
                                <input type="number" class="form-control calc-input" id="other_allowance" value="0" min="0" step="0.01">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="hrm-section-title deductions"><i class="bx bx-minus-circle"></i> Deductions</div>
                            <div class="mb-3">
                                <label class="form-label" for="pf">PF (Provident Fund)</label>
                                <input type="number" class="form-control calc-input" id="pf" value="0" min="0" step="0.01">
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="esi">ESI</label>
                                <input type="number" class="form-control calc-input" id="esi" value="0" min="0" step="0.01">
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="professional_tax">Professional Tax</label>
                                <input type="number" class="form-control calc-input" id="professional_tax" value="0" min="0" step="0.01">
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="tds_value">TDS</label>
                                <div class="input-group">
                                    <select class="form-select calc-input" id="tds_type" style="max-width: 160px;">
                                        <option value="percent">% of Basic + DA</option>
                                        <option value="amount">Fixed Amount</option>
                                    </select>
                                    <input type="number" class="form-control calc-input" id="tds_value" value="0" min="0" step="0.01">
                                </div>
                                <div class="letter-hint mt-1" id="tdsHint" style="font-size:12px;color:#6c757d;">TDS deduction: ₹0 (0% of Basic + DA)</div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="other_deduction">Other Deduction</label>
                                <input type="number" class="form-control calc-input" id="other_deduction" value="0" min="0" step="0.01">
                            </div>
                        </div>
                    </div>

                    <div class="hrm-summary-box">
                        <div class="hrm-summary-row">
                            <span>Gross Salary</span>
                            <span class="val-earn" id="sumGross">₹0</span>
                        </div>
                        <div class="hrm-summary-row">
                            <span>Total Deductions</span>
                            <span class="val-ded" id="sumDeductions">₹0</span>
                        </div>
                        <div class="hrm-summary-row net">
                            <span>Net Salary</span>
                            <span class="val-net" id="sumNet">₹0</span>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary btn-sm" onclick="saveStructure()">Save</button>
            </div>
        </div>
    </div>
</div>

<!-- Advance Modal -->
<div class="modal fade" id="advanceModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bx bx-wallet me-1"></i> Record Advance / Allowance</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div><span id="advanceModalMessage"></span></div>
                <form id="advanceForm" onsubmit="return false;">
                    <input type="hidden" id="advance_id" value="">
                    <div class="mb-3">
                        <label class="form-label" for="advance_employee_id">Employee <span class="text-danger">*</span></label>
                        <select class="form-select" id="advance_employee_id"></select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="advance_type">Type <span class="text-danger">*</span></label>
                        <select class="form-select" id="advance_type">
                            <option value="Salary Advance">Salary Advance</option>
                            <option value="Loan">Loan</option>
                            <option value="Travel Advance">Travel Advance</option>
                            <option value="Festival Advance">Festival Advance</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label" for="advance_amount">Amount <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="advance_amount" min="0" step="0.01">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label" for="advance_monthly_deduction">Monthly Deduction</label>
                                <input type="number" class="form-control" id="advance_monthly_deduction" value="0" min="0" step="0.01">
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="advance_date">Date</label>
                        <input type="date" class="form-control" id="advance_date" value="<?php echo date('Y-m-d'); ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="advance_reason">Reason / Notes</label>
                        <input type="text" class="form-control" id="advance_reason" placeholder="e.g. Medical emergency">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-warning btn-sm" onclick="saveAdvance()"><i class="bx bx-check"></i> Save Advance</button>
            </div>
        </div>
    </div>
</div>

<?php include 'layouts/right-sidebar.php'; ?>
<?php include 'layouts/vendor-scripts.php'; ?>
<script src="assets/js/app.js"></script>
<script>
function escapeHtml(text) {
    if (text === null || text === undefined) return '';
    return String(text)
        .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;').replace(/'/g, '&#039;');
}

function fmtMoney(n) {
    n = parseFloat(n) || 0;
    return '₹' + n.toLocaleString('en-IN', { maximumFractionDigits: 2 });
}

var structureModal, advanceModal;
document.addEventListener('DOMContentLoaded', function() {
    structureModal = new bootstrap.Modal(document.getElementById('structureModal'));
    advanceModal = new bootstrap.Modal(document.getElementById('advanceModal'));
});

function loadEmployeeOptions(selectedId, selectId) {
    selectId = selectId || 'employee_id';
    return fetch('hrm-api.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'listactiveemployees' })
    })
    .then(function(r) { return r.json(); })
    .then(function(res) {
        var sel = document.getElementById(selectId);
        var opts = '<option value="">Select Employee</option>';
        (res.data || []).forEach(function(e) {
            var label = escapeHtml(e.sFullName) + (e.sEmployeeCode ? ' (' + escapeHtml(e.sEmployeeCode) + ')' : '');
            opts += '<option value="' + e.iEmployeeid + '">' + label + '</option>';
        });
        sel.innerHTML = opts;
        if (selectedId) sel.value = selectedId;
    });
}

function recalcTotals() {
    function v(id) { return parseFloat(document.getElementById(id).value) || 0; }
    var basic = v('basic_salary'), hra = v('hra'), da = v('da'), travel = v('travel_allowance'),
        medical = v('medical_allowance'), special = v('special_allowance'), otherAllow = v('other_allowance');
    var pf = v('pf'), esi = v('esi'), pt = v('professional_tax'), tdsVal = v('tds_value'), otherDed = v('other_deduction');
    var tdsType = document.getElementById('tds_type').value;

    var gross = basic + hra + da + travel + medical + special + otherAllow;
    var tds = tdsType === 'percent' ? (basic + da) * (tdsVal / 100) : tdsVal;
    var deductions = pf + esi + pt + tds + otherDed;
    var net = gross - deductions;

    document.getElementById('sumGross').textContent = fmtMoney(gross);
    document.getElementById('sumDeductions').textContent = fmtMoney(deductions);
    document.getElementById('sumNet').textContent = fmtMoney(net);
    document.getElementById('tdsHint').textContent = 'TDS deduction: ' + fmtMoney(tds) + ' (' + (tdsType === 'percent' ? tdsVal + '% of Basic + DA' : 'fixed amount') + ')';
}

document.addEventListener('input', function(e) {
    if (e.target && e.target.classList.contains('calc-input')) recalcTotals();
});
document.addEventListener('change', function(e) {
    if (e.target && e.target.classList.contains('calc-input')) recalcTotals();
});

function resetStructureForm() {
    document.getElementById('structureForm').reset();
    document.getElementById('structure_id').value = '';
    document.getElementById('effective_from').value = '<?php echo date('Y-m-d'); ?>';
    ['basic_salary','hra','da','travel_allowance','medical_allowance','special_allowance','other_allowance',
     'pf','esi','professional_tax','tds_value','other_deduction'].forEach(function(id) {
        document.getElementById(id).value = 0;
    });
    document.getElementById('tds_type').value = 'percent';
    document.getElementById('modalMessage').innerHTML = '';
    recalcTotals();
}

function openAddStructure() {
    resetStructureForm();
    loadEmployeeOptions().then(function() {
        structureModal.show();
    });
}

function openEditStructure(id) {
    fetch('hrm-api.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'getstructurebyid', id: id })
    })
    .then(function(r) { return r.json(); })
    .then(function(res) {
        if (res.status !== 'success') { alert(res.message || 'Unable to load.'); return; }
        var d = res.data;
        resetStructureForm();
        loadEmployeeOptions(d.iEmployeeid).then(function() {
            document.getElementById('structure_id').value = d.iStructureid;
            document.getElementById('effective_from').value = d.dEffectiveFrom;
            document.getElementById('basic_salary').value = d.fBasicSalary;
            document.getElementById('hra').value = d.fHRA;
            document.getElementById('da').value = d.fDA;
            document.getElementById('travel_allowance').value = d.fTravelAllowance;
            document.getElementById('medical_allowance').value = d.fMedicalAllowance;
            document.getElementById('special_allowance').value = d.fSpecialAllowance;
            document.getElementById('other_allowance').value = d.fOtherAllowance;
            document.getElementById('pf').value = d.fPF;
            document.getElementById('esi').value = d.fESI;
            document.getElementById('professional_tax').value = d.fProfessionalTax;
            document.getElementById('tds_type').value = d.sTdsType;
            document.getElementById('tds_value').value = d.fTdsValue;
            document.getElementById('other_deduction').value = d.fOtherDeduction;
            recalcTotals();
            structureModal.show();
        });
    });
}

function saveStructure() {
    var employeeId = document.getElementById('employee_id').value;
    var effectiveFrom = document.getElementById('effective_from').value;
    if (!employeeId) { alert('Please select an employee.'); return; }
    if (!effectiveFrom) { alert('Please choose an effective date.'); return; }

    var id = document.getElementById('structure_id').value;
    function v(fid) { return document.getElementById(fid).value; }

    var data = {
        action: id ? 'updatestructure' : 'savestructure',
        id: id,
        employee_id: employeeId,
        effective_from: effectiveFrom,
        basic_salary: v('basic_salary'),
        hra: v('hra'),
        da: v('da'),
        travel_allowance: v('travel_allowance'),
        medical_allowance: v('medical_allowance'),
        special_allowance: v('special_allowance'),
        other_allowance: v('other_allowance'),
        pf: v('pf'),
        esi: v('esi'),
        professional_tax: v('professional_tax'),
        tds_type: v('tds_type'),
        tds_value: v('tds_value'),
        other_deduction: v('other_deduction')
    };

    fetch('hrm-api.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
    })
    .then(function(r) { return r.json(); })
    .then(function(res) {
        var el = document.getElementById('modalMessage');
        el.innerHTML = res.message || '';
        el.className = res.status === 'success' ? 'success-message' : 'error-message';
        if (res.status === 'success') {
            setTimeout(function() {
                structureModal.hide();
                loadStructures();
            }, 500);
        }
    })
    .catch(function() {
        document.getElementById('modalMessage').innerHTML = 'Save failed.';
    });
}

function deleteStructure(id) {
    if (!id || !confirm('Delete this salary structure?')) return;
    fetch('hrm-api.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'deletestructure', id: id })
    })
    .then(function(r) { return r.json(); })
    .then(function(res) {
        var el = document.getElementById('message');
        el.innerHTML = res.message || '';
        el.className = res.status === 'success' ? 'success-message' : 'error-message';
        loadStructures();
    });
}

function loadStructures() {
    $.ajax({
        url: 'hrm-api.php',
        method: 'POST',
        contentType: 'application/json',
        data: JSON.stringify({ action: 'liststructures' }),
        success: function(response) {
            var rows = '';
            if (response.status === 'success') {
                var list = response.data || [];
                if (!list.length) {
                    rows = '<tr><td colspan="8" class="text-center">No salary structures found</td></tr>';
                } else {
                    list.forEach(function(r, index) {
                        var name = escapeHtml(r.sFullName || '') + (r.sEmployeeCode ? ' (' + escapeHtml(r.sEmployeeCode) + ')' : '');
                        rows += '<tr>';
                        rows += '<td>' + (index + 1) + '</td>';
                        rows += '<td>' + name + '</td>';
                        rows += '<td>' + escapeHtml(r.dEffectiveFrom || '') + '</td>';
                        rows += '<td>' + fmtMoney(r.fGrossSalary) + '</td>';
                        rows += '<td>' + fmtMoney(r.fTotalDeductions) + '</td>';
                        rows += '<td>' + fmtMoney(r.fNetSalary) + '</td>';
                        rows += '<td><button class="btn btn-success btn-sm" onclick="openEditStructure(' + r.iStructureid + ')">Edit</button></td>';
                        rows += '<td><button class="btn btn-danger btn-sm" onclick="deleteStructure(' + r.iStructureid + ')">Delete</button></td>';
                        rows += '</tr>';
                    });
                }
            } else {
                rows = '<tr><td colspan="8" class="text-center">No salary structures found</td></tr>';
            }
            if ($.fn.DataTable.isDataTable('#datatable')) {
                $('#datatable').DataTable().destroy();
            }
            $('#datatable tbody').html(rows);
            if (response.status === 'success' && (response.data || []).length) {
                $('#datatable').DataTable({ order: [[0, 'asc']] });
            }
        },
        error: function() {
            alert('Failed to load salary structures.');
        }
    });
}

function resetAdvanceForm() {
    document.getElementById('advanceForm').reset();
    document.getElementById('advance_id').value = '';
    document.getElementById('advance_date').value = '<?php echo date('Y-m-d'); ?>';
    document.getElementById('advance_type').value = 'Salary Advance';
    document.getElementById('advance_monthly_deduction').value = 0;
    document.getElementById('advanceModalMessage').innerHTML = '';
}

function openAddAdvance() {
    resetAdvanceForm();
    document.querySelector('#advanceModal .modal-title').innerHTML = '<i class="bx bx-wallet me-1"></i> Record Advance / Allowance';
    loadEmployeeOptions(null, 'advance_employee_id').then(function() {
        advanceModal.show();
    });
}

function openEditAdvance(id) {
    fetch('hrm-api.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'getadvancebyid', id: id })
    })
    .then(function(r) { return r.json(); })
    .then(function(res) {
        if (res.status !== 'success') { alert(res.message || 'Unable to load.'); return; }
        var d = res.data;
        resetAdvanceForm();
        document.querySelector('#advanceModal .modal-title').innerHTML = '<i class="bx bx-wallet me-1"></i> Edit Advance / Allowance';
        loadEmployeeOptions(d.iEmployeeid, 'advance_employee_id').then(function() {
            document.getElementById('advance_id').value = d.iAdvanceid;
            document.getElementById('advance_type').value = d.sType;
            document.getElementById('advance_amount').value = d.fAmount;
            document.getElementById('advance_monthly_deduction').value = d.fMonthlyDeduction;
            document.getElementById('advance_date').value = d.dDate || '<?php echo date('Y-m-d'); ?>';
            document.getElementById('advance_reason').value = d.sReason || '';
            advanceModal.show();
        });
    });
}

function saveAdvance() {
    var employeeId = document.getElementById('advance_employee_id').value;
    var amount = document.getElementById('advance_amount').value;
    if (!employeeId) { alert('Please select an employee.'); return; }
    if (!amount || parseFloat(amount) <= 0) { alert('Please enter a valid amount.'); return; }

    var id = document.getElementById('advance_id').value;
    var data = {
        action: id ? 'updateadvance' : 'saveadvance',
        id: id,
        employee_id: employeeId,
        type: document.getElementById('advance_type').value,
        amount: amount,
        monthly_deduction: document.getElementById('advance_monthly_deduction').value,
        date: document.getElementById('advance_date').value,
        reason: document.getElementById('advance_reason').value
    };

    fetch('hrm-api.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
    })
    .then(function(r) { return r.json(); })
    .then(function(res) {
        var el = document.getElementById('advanceModalMessage');
        el.innerHTML = res.message || '';
        el.className = res.status === 'success' ? 'success-message' : 'error-message';
        if (res.status === 'success') {
            setTimeout(function() {
                advanceModal.hide();
                loadAdvances();
            }, 500);
        }
    })
    .catch(function() {
        document.getElementById('advanceModalMessage').innerHTML = 'Save failed.';
    });
}

function recordRepayment(id) {
    var amount = prompt('Enter repayment amount received:');
    if (amount === null) return;
    amount = parseFloat(amount);
    if (!amount || amount <= 0) { alert('Please enter a valid amount.'); return; }

    fetch('hrm-api.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'recordadvancerepayment', id: id, amount: amount })
    })
    .then(function(r) { return r.json(); })
    .then(function(res) {
        var el = document.getElementById('message');
        el.innerHTML = res.message || '';
        el.className = res.status === 'success' ? 'success-message' : 'error-message';
        loadAdvances();
    });
}

function deleteAdvance(id) {
    if (!id || !confirm('Delete this advance record?')) return;
    fetch('hrm-api.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'deleteadvance', id: id })
    })
    .then(function(r) { return r.json(); })
    .then(function(res) {
        var el = document.getElementById('message');
        el.innerHTML = res.message || '';
        el.className = res.status === 'success' ? 'success-message' : 'error-message';
        loadAdvances();
    });
}

function loadAdvances() {
    fetch('hrm-api.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'listadvances' })
    })
    .then(function(r) { return r.json(); })
    .then(function(res) {
        var rows = '';
        if (res.status === 'success' && (res.data || []).length) {
            res.data.forEach(function(a) {
                var name = escapeHtml(a.sFullName || '') + (a.sEmployeeCode ? ' (' + escapeHtml(a.sEmployeeCode) + ')' : '');
                var badge = a.sStatus === 'Closed' ? 'badge-paid' : 'badge-pending';
                rows += '<tr>';
                rows += '<td>' + name + '<br><span class="badge ' + badge + '">' + escapeHtml(a.sStatus) + '</span></td>';
                rows += '<td>' + escapeHtml(a.sType || '') + '</td>';
                rows += '<td>' + fmtMoney(a.fOutstanding) + '</td>';
                rows += '<td class="text-nowrap">';
                if (a.sStatus !== 'Closed') {
                    rows += '<button class="btn btn-success btn-sm mb-1" onclick="recordRepayment(' + a.iAdvanceid + ')">Repay</button> ';
                }
                rows += '<button class="btn btn-secondary btn-sm mb-1" onclick="openEditAdvance(' + a.iAdvanceid + ')">Edit</button> ';
                rows += '<button class="btn btn-danger btn-sm mb-1" onclick="deleteAdvance(' + a.iAdvanceid + ')">Delete</button>';
                rows += '</td>';
                rows += '</tr>';
            });
        } else {
            rows = '<tr><td colspan="4" class="text-center">No advances recorded</td></tr>';
        }
        document.querySelector('#advanceTable tbody').innerHTML = rows;
    });
}

function runPayroll() {
    var month = document.getElementById('payrollMonth').value;
    if (!month) { alert('Please select a pay month.'); return; }
    if (!confirm('Run payroll for ' + month + '? This will generate payslips for all employees with an active salary structure.')) return;

    fetch('hrm-api.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'runpayroll', month: month })
    })
    .then(function(r) { return r.json(); })
    .then(function(res) {
        var el = document.getElementById('message');
        el.innerHTML = res.message || '';
        el.className = res.status === 'success' ? 'success-message' : 'error-message';
        if (res.status === 'success') {
            setTimeout(function() { window.location.href = 'list-payroll.php'; }, 900);
        }
    })
    .catch(function() { alert('Payroll run failed.'); });
}

$(document).ready(function() {
    loadStructures();
    loadAdvances();
});
</script>
</body>
</html>
