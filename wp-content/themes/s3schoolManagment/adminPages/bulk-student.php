<?php

/**
 * Template Name: Bulk Student Add/Edit
 */

global $wpdb;

// Increase limits for large bulk operations
@ini_set('memory_limit', '512M');
@set_time_limit(300);

/* =================
   Process: Load existing students for editing
   ================= */
$loadClass  = isset($_POST['loadClass'])  ? intval($_POST['loadClass'])  : (isset($_GET['class'])  ? intval($_GET['class'])  : 0);
$loadSection = isset($_POST['loadSection']) ? intval($_POST['loadSection']) : (isset($_GET['section']) ? intval($_GET['section']) : 0);
$loadYear   = isset($_POST['loadYear'])   ? sanitize_text_field($_POST['loadYear'])   : (isset($_GET['year'])   ? sanitize_text_field($_GET['year'])   : '');
$loadGroup  = isset($_POST['loadGroup'])  ? intval($_POST['loadGroup'])  : (isset($_GET['group'])  ? intval($_GET['group'])  : 0);

$editing    = false;
$editStudents = array();

if (isset($_POST['loadStudents']) && $loadClass > 0 && !empty($loadYear)) {
    $editing = true;

    $group_condition = $loadGroup > 0 ? $wpdb->prepare(" AND ct_studentinfo.infoGroup = %d", $loadGroup) : '';
    $section_condition = $loadSection > 0 ? $wpdb->prepare(" AND ct_studentinfo.infoSection = %d", $loadSection) : '';

    $editStudents = $wpdb->get_results($wpdb->prepare("
        SELECT ct_student.*, ct_studentinfo.*
        FROM ct_student
        LEFT JOIN ct_studentinfo ON ct_student.studentid = ct_studentinfo.infoStdid
            AND ct_studentinfo.infoClass = %d
            AND ct_studentinfo.infoYear = %s
        WHERE ct_student.stdCurrentClass = %d
            AND ct_student.stdCurntYear = %s
            $section_condition
            $group_condition
        ORDER BY ct_studentinfo.infoRoll ASC
    ", $loadClass, $loadYear, $loadClass, $loadYear));

    if (empty($editStudents)) {
        $message = array(
            'status'  => 'faild',
            'message' => 'No students found for the selected class, section & year.'
        );
    }
}


/* =================
   Process: Bulk Save
   ================= */
if (isset($_POST['bulkSaveStudents'])) {
    $commonClass   = intval($_POST['bulkClass']);
    $commonSection = intval($_POST['bulkSection']);
    $commonYear    = sanitize_text_field($_POST['bulkYear']);
    $commonGroup   = isset($_POST['bulkGroup']) ? intval($_POST['bulkGroup']) : 0;

    $stdNames          = isset($_POST['stdName'])          ? $_POST['stdName']          : array();
    $stdNamesBn        = isset($_POST['stdNameBangla'])    ? $_POST['stdNameBangla']    : array();
    $stdFacilities     = isset($_POST['facilities'])       ? $_POST['facilities']       : array();
    $stdGenders        = isset($_POST['stdGender'])        ? $_POST['stdGender']        : array();
    $stdBirthRegNos    = isset($_POST['birth_reg_no'])     ? $_POST['birth_reg_no']     : array();
    $stdBirths         = isset($_POST['stdBrith'])         ? $_POST['stdBrith']         : array();
    $stdBldGrps        = isset($_POST['stdBldGrp'])        ? $_POST['stdBldGrp']        : array();
    $stdReligions      = isset($_POST['stdReligion'])      ? $_POST['stdReligion']      : array();
    $stdRolls          = isset($_POST['stdRoll'])          ? $_POST['stdRoll']          : array();
    $stdPresent        = isset($_POST['stdPresent'])       ? $_POST['stdPresent']       : array();
    $stdFathers        = isset($_POST['stdFather'])        ? $_POST['stdFather']        : array();
    $stdMothers        = isset($_POST['stdMother'])        ? $_POST['stdMother']        : array();
    $stdPhones         = isset($_POST['stdPhone'])         ? $_POST['stdPhone']         : array();
    $stdEmergencyPhones = isset($_POST['stdEmergencyPhone']) ? $_POST['stdEmergencyPhone'] : array();
    $stdIds            = isset($_POST['stdid'])            ? $_POST['stdid']            : array();
    $stdInfoIds        = isset($_POST['infoid'])           ? $_POST['infoid']           : array();
    $std4thSubs        = isset($_POST['std4thsub'])        ? $_POST['std4thsub']        : array();
    $stdShift          = isset($_POST['stdShift'])         ? sanitize_text_field($_POST['stdShift']) : '';

    $inserted   = 0;
    $updated    = 0;
    $errors     = 0;
    $currentUserId = get_current_user_id();

    foreach ($stdNames as $index => $name) {
        $name = trim($name);
        if (empty($name)) continue;

        $studentId = isset($stdIds[$index]) ? intval($stdIds[$index]) : 0;
        $infoId    = isset($stdInfoIds[$index]) ? intval($stdInfoIds[$index]) : 0;
        $isUpdate  = ($studentId > 0);

        // Prepare student data
        $studentData = array(
            'stdName'         => $name,
            'stdNameBangla'   => isset($stdNamesBn[$index]) ? sanitize_text_field($stdNamesBn[$index]) : '',
            'stdFather'       => isset($stdFathers[$index]) ? sanitize_text_field($stdFathers[$index]) : '',
            'stdMother'       => isset($stdMothers[$index]) ? sanitize_text_field($stdMothers[$index]) : '',
            'stdPhone'        => isset($stdPhones[$index]) ? sanitize_text_field($stdPhones[$index]) : '',
            'stdEmergencyPhone' => isset($stdEmergencyPhones[$index]) ? sanitize_text_field($stdEmergencyPhones[$index]) : '',
            'stdPresent'      => isset($stdPresent[$index]) ? sanitize_textarea_field($stdPresent[$index]) : '',
            'stdBrith'        => isset($stdBirths[$index]) ? sanitize_text_field($stdBirths[$index]) : '',
            'birth_reg_no'    => isset($stdBirthRegNos[$index]) ? sanitize_text_field($stdBirthRegNos[$index]) : '',
            'facilities'      => isset($stdFacilities[$index]) ? sanitize_text_field($stdFacilities[$index]) : 'None',
            'stdGender'       => isset($stdGenders[$index]) ? intval($stdGenders[$index]) : 1,
            'stdBldGrp'       => isset($stdBldGrps[$index]) ? sanitize_text_field($stdBldGrps[$index]) : 'N/A',
            'stdReligion'     => isset($stdReligions[$index]) ? sanitize_text_field($stdReligions[$index]) : '',
            'stdCurntYear'    => $commonYear,
            'stdCurrentClass' => $commonClass,
            'stdShift'        => $stdShift,
        );

        if ($isUpdate) {
            // --- UPDATE existing student ---
            $studentData['stdUpdatedAt'] = current_time('mysql');
            $updatedDb = $wpdb->update('ct_student', $studentData, array('studentid' => $studentId));

            // Update ct_studentinfo
            $infoData = array(
                'infoClass'   => $commonClass,
                'infoSection' => $commonSection,
                'infoGroup'   => $commonGroup,
                'infoRoll'    => isset($stdRolls[$index]) ? sanitize_text_field($stdRolls[$index]) : '',
                'infoYear'    => $commonYear,
                'info4thSub'  => isset($std4thSubs[$index]) && is_string($std4thSubs[$index]) ? $std4thSubs[$index] : (isset($std4thSubs[$index]) ? json_encode($std4thSubs[$index]) : 0),
            );

            if ($infoId > 0) {
                $wpdb->update('ct_studentinfo', $infoData, array('infoid' => $infoId));
            } else {
                $infoData['infoStdid'] = $studentId;
                $infoData['infoOptionals'] = 0;
                $wpdb->insert('ct_studentinfo', $infoData);
            }

            if ($updatedDb !== false) $updated++;
            else $errors++;
        } else {
            // --- INSERT new student ---
            $studentData['stdAdmitYear']  = $commonYear;
            $studentData['stdAdmitClass'] = $commonClass;
            $studentData['stdNameBangla'] = isset($stdNamesBn[$index]) ? sanitize_text_field($stdNamesBn[$index]) : '';
            $studentData['stdPermanent']  = '';
            $studentData['stdNationality'] = 'Bangladeshi';
            $studentData['stdGender']     = isset($stdGenders[$index]) ? intval($stdGenders[$index]) : 1;
            $studentData['createdBy']     = $currentUserId;

            $insertDb = $wpdb->insert('ct_student', $studentData);
            if ($insertDb) {
                $newId = $wpdb->insert_id;

                $wpdb->insert('ct_studentinfo', array(
                    'infoStdid'    => $newId,
                    'infoClass'    => $commonClass,
                    'infoSection'  => $commonSection,
                    'infoGroup'    => $commonGroup,
                    'infoRoll'     => isset($stdRolls[$index]) ? sanitize_text_field($stdRolls[$index]) : '',
                    'infoYear'     => $commonYear,
                    'infoOptionals' => 0,
                    'info4thSub'   => isset($std4thSubs[$index]) ? json_encode($std4thSubs[$index]) : 0,
                ));

                $inserted++;
            } else {
                $errors++;
            }
        }
    }

    $message = array(
        'status'  => ($errors === 0) ? 'success' : 'faild',
        'message' => "Inserted: {$inserted}, Updated: {$updated}, Errors: {$errors}"
    );
}


/* =================
   Get reference data
   ================= */
$groups  = $wpdb->get_results("SELECT groupId, groupName FROM ct_group ORDER BY groupName");
$religions = array('Muslim', 'Hinduism', 'Buddist', 'Christian', 'other');

?>


<?php if (!is_admin()) { get_header(); ?>
<div class="b-layer-main">
    <div class="container">
        <div class="row">
            <div class="col-md-12">
<?php } ?>

<p id="theSiteURL" class="hidden"><?= get_template_directory_uri() ?></p>

<div class="container-fluid maxAdminpages" style="padding-left:0">

    <!-- Status Message -->
    <?php if (isset($message)) { ms3showMessage($message); } ?>

    <h2>
        Bulk Student Add / Edit
        <a class="pull-right btn btn-primary" href="?page=student">
            <span class="dashicons dashicons-groups"></span> Back to Students
        </a>
    </h2>

    <hr>

    <!-- ============================================ -->
    <!-- Step 1: Select Class, Section, Year to Load  -->
    <!-- ============================================ -->
    <div class="panel panel-default">
        <div class="panel-body">
            <form method="POST" class="form-inline" style="display:flex; flex-wrap:wrap; gap:12px; align-items:flex-end;">

                <div class="form-group">
                    <label>Class &nbsp;</label>
                    <select name="loadClass" id="bulkClass" class="form-control" required style="min-width:160px;">
                        <option value="">— Select Class —</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Year &nbsp;</label>
                    <select name="loadYear" id="bulkYear" class="form-control" required style="min-width:140px;">
                        <option value="">Year</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Section &nbsp;</label>
                    <select name="loadSection" id="bulkSection" class="form-control" style="min-width:140px;">
                        <option value="0">Section</option>
                    </select>
                </div>

                <div class="form-group" id="bulkGroupWrap" style="display:none;">
                    <label>Group &nbsp;</label>
                    <select name="loadGroup" id="bulkGroup" class="form-control" style="min-width:140px;">
                        <option value="0">— All Groups —</option>
                        <?php foreach ($groups as $g) {
                            $sel = ($loadGroup == $g->groupId) ? 'selected' : '';
                            echo "<option value='{$g->groupId}' {$sel}>{$g->groupName}</option>";
                        } ?>
                    </select>
                </div>

                <button type="submit" name="loadStudents" class="btn btn-info">
                    <span class="dashicons dashicons-search"></span> Load / Edit
                </button>

                <button type="button" class="btn btn-success" id="clearTableBtn">
                    <span class="dashicons dashicons-plus"></span> Start Fresh (Add New)
                </button>
            </form>
        </div>
    </div>


    <!-- ============================================ -->
    <!-- Step 2: Bulk Data Entry Table                -->
    <!-- ============================================ -->
    <div class="panel panel-default" id="bulkTablePanel" style="<?= (isset($_POST['loadStudents']) || isset($_POST['bulkSaveStudents'])) ? '' : 'display:none;' ?>">
        <div class="panel-body">

            <form method="POST" id="bulkForm" enctype="multipart/form-data">

                <!-- Hidden common values -->
                <input type="hidden" name="bulkClass" id="hiddenClass" value="<?= $loadClass ?>">
                <input type="hidden" name="bulkSection" id="hiddenSection" value="<?= $loadSection ?>">
                <input type="hidden" name="bulkYear" id="hiddenYear" value="<?= esc_attr($loadYear) ?>">
                <input type="hidden" name="bulkGroup" id="hiddenGroup" value="<?= $loadGroup ?>">

                <div style="margin-bottom:12px;">
                    <button type="button" class="btn btn-primary" id="addRowBtn">
                        <span class="dashicons dashicons-plus-alt"></span> Add Row
                    </button>
                    <button type="button" class="btn btn-warning" id="removeLastRowBtn">
                        <span class="dashicons dashicons-remove"></span> Remove Last
                    </button>
                    <button type="submit" name="bulkSaveStudents" class="btn btn-success pull-right" id="saveAllBtn">
                        <span class="dashicons dashicons-saved"></span> Save All Students
                    </button>
                </div>

                <div class="table-responsive" style="border:1px solid #ddd;">
                    <table class="table table-bordered table-striped table-condensed" id="bulkStudentTable" style="margin-bottom:0; white-space:nowrap;">
                        <thead style="position:sticky; top:0; background:#fff; z-index:2;">
                            <tr>
                                <th style="min-width:20px;">#</th>
                                <th style="min-width:150px;">Student Name <span class="text-danger">*</span></th>
                                <th style="min-width:130px;">Name (BN)</th>
                                <th style="min-width:90px;">Facilities</th>
                                <th style="min-width:70px;">Gender</th>
                                <th style="min-width:120px;">Birth Reg No</th>
                                <th style="min-width:110px;">Date of Birth</th>
                                <th style="min-width:70px;">Blood Grp</th>
                                <th style="min-width:90px;">Religion</th>
                                <th style="min-width:90px;">Roll / ID</th>
                                <th style="min-width:120px;">Group</th>
                                <th style="min-width:180px;">Present Address</th>
                                <th style="min-width:140px;">Father's Name</th>
                                <th style="min-width:140px;">Mother's Name</th>
                                <th style="min-width:120px;">Phone</th>
                                <th style="min-width:120px;">Emergency Phone</th>
                            </tr>
                        </thead>
                        <tbody id="bulkTbody">
                            <?php if ($editing && !empty($editStudents)): ?>
                                <?php $rowNum = 0; ?>
                                <?php foreach ($editStudents as $s):
                                    $rowNum++;
                                ?>
                                <tr class="bulk-row">
                                    <td class="row-index"><?= $rowNum ?></td>
                                    <td>
                                        <input type="hidden" name="stdid[]" value="<?= $s->studentid ?>">
                                        <input type="hidden" name="infoid[]" value="<?= $s->infoid ?>">
                                        <input type="text" name="stdName[]" class="form-control input-sm" value="<?= esc_attr($s->stdName) ?>" required placeholder="Student Name">
                                    </td>
                                    <td><input type="text" name="stdNameBangla[]" class="form-control input-sm" value="<?= esc_attr($s->stdNameBangla) ?>" placeholder="বাংলা নাম"></td>
                                    <td>
                                        <select name="facilities[]" class="form-control input-sm">
                                            <option value="None" <?= ($s->facilities == '' || $s->facilities == 'None') ? 'selected' : '' ?>>None</option>
                                            <option value="Scholarship" <?= $s->facilities == 'Scholarship' ? 'selected' : '' ?>>Scholarship</option>
                                            <option value="Full free" <?= $s->facilities == 'Full free' ? 'selected' : '' ?>>Full free</option>
                                            <option value="Half free" <?= $s->facilities == 'Half free' ? 'selected' : '' ?>>Half free</option>
                                        </select>
                                    </td>
                                    <td>
                                        <select name="stdGender[]" class="form-control input-sm">
                                            <option value="1" <?= ($s->stdGender == 1) ? 'selected' : '' ?>>Boy</option>
                                            <option value="0" <?= ($s->stdGender == 0) ? 'selected' : '' ?>>Girl</option>
                                            <option value="2" <?= ($s->stdGender == 2) ? 'selected' : '' ?>>Other</option>
                                        </select>
                                    </td>
                                    <td><input type="text" name="birth_reg_no[]" class="form-control input-sm" value="<?= esc_attr($s->birth_reg_no) ?>" placeholder="Birth Reg No"></td>
                                    <td><input type="date" name="stdBrith[]" class="form-control input-sm" value="<?= esc_attr($s->stdBrith) ?>"></td>
                                    <td>
                                        <select name="stdBldGrp[]" class="form-control input-sm">
                                            <option>N/A</option>
                                            <?php foreach (array('A+','A-','B+','B-','AB+','AB-','O+','O-') as $bg) {
                                                $sel = ($s->stdBldGrp == $bg) ? 'selected' : '';
                                                echo "<option {$sel}>{$bg}</option>";
                                            } ?>
                                        </select>
                                    </td>
                                    <td>
                                        <select name="stdReligion[]" class="form-control input-sm">
                                            <option value="">—</option>
                                            <?php foreach ($religions as $r) {
                                                $sel = ($s->stdReligion == $r) ? 'selected' : '';
                                                echo "<option value='{$r}' {$sel}>{$r}</option>";
                                            } ?>
                                        </select>
                                    </td>
                                    <td><input type="text" name="stdRoll[]" class="form-control input-sm" value="<?= esc_attr($s->infoRoll) ?>" placeholder="Roll"></td>
                                    <td>
                                        <select name="stdGroup[]" class="form-control input-sm">
                                            <option value="0">—</option>
                                            <?php foreach ($groups as $g) {
                                                $sel = ($s->infoGroup == $g->groupId) ? 'selected' : '';
                                                echo "<option value='{$g->groupId}' {$sel}>{$g->groupName}</option>";
                                            } ?>
                                        </select>
                                    </td>
                                    <td><input type="text" name="stdPresent[]" class="form-control input-sm" value="<?= esc_attr($s->stdPresent) ?>" placeholder="Present Address"></td>
                                    <td><input type="text" name="stdFather[]" class="form-control input-sm" value="<?= esc_attr($s->stdFather) ?>" placeholder="Father's Name"></td>
                                    <td><input type="text" name="stdMother[]" class="form-control input-sm" value="<?= esc_attr($s->stdMother) ?>" placeholder="Mother's Name"></td>
                                    <td><input type="text" name="stdPhone[]" class="form-control input-sm" value="<?= esc_attr($s->stdPhone) ?>" placeholder="Phone"></td>
                                    <td><input type="text" name="stdEmergencyPhone[]" class="form-control input-sm" value="<?= esc_attr($s->stdEmergencyPhone) ?>" placeholder="Emergency Phone"></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <div style="margin-top:12px;">
                    <button type="button" class="btn btn-primary" id="addRowBtn2">
                        <span class="dashicons dashicons-plus-alt"></span> Add Row
                    </button>
                    <button type="button" class="btn btn-warning" id="removeLastRowBtn2">
                        <span class="dashicons dashicons-remove"></span> Remove Last
                    </button>
                    <button type="submit" name="bulkSaveStudents" class="btn btn-success pull-right">
                        <span class="dashicons dashicons-saved"></span> Save All Students
                    </button>
                </div>

            </form>
        </div>
    </div>

</div><!-- .maxAdminpages -->


<?php if (!is_admin()) { ?>
            </div>
        </div>
    </div>
</div>
<?php get_footer(); } ?>


<!-- ============================================ -->
<!-- JavaScript                                   -->
<!-- ============================================ -->
<script type="text/javascript">
(function($) {

    var siteUrl = '<?= get_template_directory_uri() ?>';

    /* ---------- Pre-populated groups for the Group column ---------- */
    var allGroups = <?php echo json_encode(array_map(function($g) { return ['id' => $g->groupId, 'name' => $g->groupName]; }, $groups)); ?>;

    /* ---------- Pre-selected values (from POST-back) ---------- */
    var initClass   = '<?= esc_attr($loadClass) ?>' || '';
    var initSection = '<?= esc_attr($loadSection) ?>' || '';
    var initYear    = '<?= esc_attr($loadYear) ?>' || '';
    var initGroup   = '<?= esc_attr($loadGroup) ?>' || '';

    /* ---------- AJAX: Load classes ---------- */
    function loadClasses(selectedVal) {
        $.post(siteUrl + '/inc/ajaxAction.php', { type: 'getClass' }, function(html) {
            $('#bulkClass').html(html);
            if (selectedVal) {
                $('#bulkClass').val(selectedVal);
            }
            // Pass stored init values so post-back selections are restored
            onClassChange(initSection, initYear, initGroup);
        });
    }

    /* ---------- AJAX: Load sections by class ---------- */
    function loadSections(classId, selectedVal) {
        var $sel = $('#bulkSection');
        if (!classId) {
            $sel.html('<option value="0">— All Sections —</option>');
            return;
        }
        $.post(siteUrl + '/inc/ajaxAction.php', { type: 'getSection', class: classId }, function(html) {
            $sel.html('<option value="0">— All Sections —</option>');
            // Strip the placeholder <option> from the response (we have our own)
            var clean = html.replace(/<option[^>]*>.*?<\/option>/, '');
            if (clean.trim()) $sel.append(clean);
            if (selectedVal) $sel.val(selectedVal);
        });
    }

    /* ---------- AJAX: Load years by class ---------- */
    function loadYears(classId, selectedVal) {
        var $sel = $('#bulkYear');
        if (!classId) {
            $sel.html('<option value="">— Year —</option>');
            return;
        }
        $.post(siteUrl + '/inc/ajaxAction.php', { type: 'getYears', class: classId }, function(html) {
            $sel.html('<option value="">— Year —</option>');
            var clean = html.replace(/<option[^>]*>.*?<\/option>/, '');
            if (clean.trim()) $sel.append(clean);
            if (selectedVal) $sel.val(selectedVal);
        });
    }

    /* ---------- AJAX: Load groups by class ---------- */
    function loadGroups(classId, selectedVal) {
        var $wrap = $('#bulkGroupWrap');
        var $sel  = $('#bulkGroup');
        if (!classId) {
            $wrap.hide();
            return;
        }
        $.post(siteUrl + '/inc/ajaxAction.php', { type: 'getGroupsByClass', class: classId }, function(html) {
            $sel.html(html);
            // Show group wrap only if there are group options
            $wrap.toggle($sel.find('option').length > 1);
            if (selectedVal) {
                $sel.val(selectedVal);
            }
        });
    }

    /* ---------- Called when class changes (or initial load) ---------- */
    function onClassChange(selectedSection, selectedYear, selectedGroup) {
        var cls = $('#bulkClass').val();
        loadSections(cls, selectedSection || '');
        loadYears(cls, selectedYear || '');
        loadGroups(cls, selectedGroup || '');
    }

    $('#bulkClass').on('change', function() {
        onClassChange('', '', '');
    });

    // Initial load on page ready
    loadClasses(initClass || '');

    /* ---------- Start Fresh ---------- */
    $('#clearTableBtn').on('click', function() {
        $('#bulkTbody').empty();
        $('#bulkTablePanel').show();
        $('#hiddenClass').val($('#bulkClass').val());
        $('#hiddenSection').val($('#bulkSection').val());
        $('#hiddenYear').val($('#bulkYear').val());
        $('#hiddenGroup').val($('#bulkGroup').val());
        addEmptyRow();
        updateRowCount();
    });

    /* ---------- Add Row ---------- */
    function addEmptyRow(values) {
        values = values || {};
        var classId = $('#bulkClass').val();
        var html = '<tr class="bulk-row">';
        html += '<td class="row-index">0</td>';
        html += '<td><input type="hidden" name="stdid[]" value="0">';
        html += '<input type="hidden" name="infoid[]" value="0">';
        html += '<input type="text" name="stdName[]" class="form-control input-sm" value="' + (values.stdName || '') + '" required placeholder="Student Name"></td>';
        html += '<td><input type="text" name="stdNameBangla[]" class="form-control input-sm" value="' + (values.stdNameBangla || '') + '" placeholder="বাংলা নাম"></td>';
        html += '<td><select name="facilities[]" class="form-control input-sm">';
        html += '<option value="None">None</option>';
        html += '<option value="Scholarship">Scholarship</option>';
        html += '<option value="Full free">Full free</option>';
        html += '<option value="Half free">Half free</option>';
        html += '</select></td>';
        html += '<td><select name="stdGender[]" class="form-control input-sm">';
        html += '<option value="1">Boy</option>';
        html += '<option value="0">Girl</option>';
        html += '<option value="2">Other</option>';
        html += '</select></td>';
        html += '<td><input type="text" name="birth_reg_no[]" class="form-control input-sm" value="' + (values.birth_reg_no || '') + '" placeholder="Birth Reg No"></td>';
        html += '<td><input type="date" name="stdBrith[]" class="form-control input-sm" value="' + (values.stdBrith || '') + '"></td>';
        html += '<td><select name="stdBldGrp[]" class="form-control input-sm">';
        html += '<option>N/A</option>';
        html += '<option>A+</option><option>A-</option><option>B+</option><option>B-</option>';
        html += '<option>AB+</option><option>AB-</option><option>O+</option><option>O-</option>';
        html += '</select></td>';
        html += '<td><select name="stdReligion[]" class="form-control input-sm">';
        html += '<option value="">—</option>';
        html += '<option value="Muslim">Muslim</option>';
        html += '<option value="Hinduism">Hinduism</option>';
        html += '<option value="Buddist">Buddist</option>';
        html += '<option value="Christian">Christian</option>';
        html += '<option value="other">Other</option>';
        html += '</select></td>';
        html += '<td><input type="text" name="stdRoll[]" class="form-control input-sm" value="' + (values.stdRoll || '') + '" placeholder="Roll"></td>';
        html += '<td><select name="stdGroup[]" class="form-control input-sm">';
        html += '<option value="0">—</option>';
        for (var gi = 0; gi < allGroups.length; gi++) {
            html += '<option value="' + allGroups[gi].id + '">' + allGroups[gi].name + '</option>';
        }
        html += '</select></td>';
        html += '<td><input type="text" name="stdPresent[]" class="form-control input-sm" value="' + (values.stdPresent || '') + '" placeholder="Present Address"></td>';
        html += '<td><input type="text" name="stdFather[]" class="form-control input-sm" value="' + (values.stdFather || '') + '" placeholder="Father\'s Name"></td>';
        html += '<td><input type="text" name="stdMother[]" class="form-control input-sm" value="' + (values.stdMother || '') + '" placeholder="Mother\'s Name"></td>';
        html += '<td><input type="text" name="stdPhone[]" class="form-control input-sm" value="' + (values.stdPhone || '') + '" placeholder="Phone"></td>';
        html += '<td><input type="text" name="stdEmergencyPhone[]" class="form-control input-sm" value="' + (values.stdEmergencyPhone || '') + '" placeholder="Emergency Phone"></td>';
        html += '</tr>';
        $('#bulkTbody').append(html);
        updateRowCount();
    }

    /* ---------- Remove Last Row ---------- */
    function removeLastRow() {
        $('#bulkTbody .bulk-row:last').remove();
        updateRowCount();
    }

    /* ---------- Update Row Count + Indexes ---------- */
    function updateRowCount() {
        var rows = $('#bulkTbody .bulk-row');
        rows.each(function(i) {
            $(this).find('.row-index').text(i + 1);
        });
        $('#rowCount').text(rows.length);
    }

    /* ---------- Event Bindings ---------- */
    $('#addRowBtn, #addRowBtn2').on('click', function() {
        $('#bulkTablePanel').show();
        addEmptyRow();
    });

    $('#removeLastRowBtn, #removeLastRowBtn2').on('click', removeLastRow);

    /* ---------- Before save: sync hidden fields ---------- */
    $('#bulkForm').on('submit', function() {
        $('#hiddenClass').val($('#bulkClass').val());
        $('#hiddenSection').val($('#bulkSection').val());
        $('#hiddenYear').val($('#bulkYear').val());
        $('#hiddenGroup').val($('#bulkGroup').val());
    });

    // Show table panel if we have loaded students
    <?php if ($editing && !empty($editStudents)): ?>
    $('#bulkTablePanel').show();
    <?php endif; ?>

    // Initial row count
    updateRowCount();

})(jQuery);
</script>

<style>
#bulkStudentTable {
    width: 100%;
    border-collapse: collapse;
}
#bulkStudentTable th {
    background: #f5f5f5;
    border-bottom: 2px solid #ddd;
    font-size: 12px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
#bulkStudentTable td {
    vertical-align: middle;
    padding: 0;
}
#bulkStudentTable .form-control.input-sm {
    font-size: 16px;
    height: 30px;
    padding: 3px 8px;
    margin: 0;
}
#bulkStudentTable select.form-control.input-sm {
    padding: 3px 4px;
    margin: 0;
}
.row-index {
    font-weight: bold;
    text-align: center;
    vertical-align: middle !important;
    font-size: 16px;
}
#bulkForm .panel-body {
    padding: 15px;
}
@media (max-width: 768px) {
    #bulkStudentTable { font-size: 12px; }
    #bulkStudentTable .form-control.input-sm { font-size: 12px; height: 28px; }
}
</style>
