<?php

/**
 * Template Name: Fast Student Entry (one-by-one)
 */

global $wpdb;

@ini_set('memory_limit', '256M');
@set_time_limit(120);

/* =================
   Get reference data
   ================= */
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

    <h2>
        Fast Student Entry
        <a class="pull-right btn btn-primary" href="/admin-student">
            <span class="dashicons dashicons-groups"></span> Back to Students
        </a>
    </h2>

    <hr>

    <!-- ============================================ -->
    <!-- Step 1: Filter (Class / Section / Year)      -->
    <!-- ============================================ -->
    <div class="panel panel-default">
        <div class="panel-heading">
            <b>1. Select Class &amp; Year</b>
        </div>
        <div class="panel-body">
            <form class="form-inline" style="display:flex; flex-wrap:wrap; gap:12px; align-items:flex-end;">

                <div class="form-group">
                    <label>Class &nbsp;</label>
                    <select id="fastClass" class="form-control" required style="min-width:160px;">
                        <option value="">— Select Class —</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Year &nbsp;</label>
                    <select id="fastYear" class="form-control" required style="min-width:140px;">
                        <option value="">— Year —</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Section &nbsp;</label>
                    <select id="fastSection" class="form-control" style="min-width:140px;">
                        <option value="0">— All Sections —</option>
                    </select>
                </div>

                <button type="button" class="btn btn-info" id="fastGoBtn">
                    <span class="dashicons dashicons-search"></span> Load Students
                </button>

                <button type="button" class="btn btn-success" style="margin-left:auto;" id="fastNewBtn">
                    <span class="dashicons dashicons-plus-alt"></span> New Entry
                </button>
            </form>
        </div>
    </div>

    <!-- ============================================ -->
    <!-- Step 2: Single Student Form                  -->
    <!-- ============================================ -->
    <div class="panel panel-default" id="fastEntryPanel" style="display:none;">
        <div class="panel-heading">
            <b>2. Student Data</b>
            <span class="pull-right">
                Student <span id="fastCurrentIdx">0</span> of <span id="fastTotalCount">0</span>
            </span>
        </div>
        <div class="panel-body">

            <form id="fastForm">
                <input type="hidden" name="fastStudentId" id="fastStudentId" value="0">
                <input type="hidden" name="fastInfoId" id="fastInfoId" value="0">
                <input type="hidden" name="fastClass" id="fastHiddenClass" value="">
                <input type="hidden" name="fastSection" id="fastHiddenSection" value="">
                <input type="hidden" name="fastYear" id="fastHiddenYear" value="">

                <!-- Student Identity -->
                <div class="fast-section">
                    <div class="fast-section-title">Student Identity</div>
                    <div class="fast-row">
                        <div class="fast-field">
                            <label>Roll / ID</label>
                            <input type="text" name="fastRoll" id="fastRoll" class="form-control" placeholder="Roll">
                        </div>
                        <div class="fast-field">
                            <label>Student Name <span class="text-danger">*</span></label>
                            <input type="text" name="fastStdName" id="fastStdName" class="form-control" required placeholder="Student Name">
                        </div>
                        <div class="fast-field">
                            <label>Name (বাংলা)</label>
                            <input type="text" name="fastStdNameBangla" id="fastStdNameBangla" class="form-control" placeholder="বাংলা নাম">
                        </div>
                        <div class="fast-field">
                            <label>Group</label>
                            <select name="fastGroup" id="fastGroup" class="form-control">
                                <option value="0">—</option>
                                <option value="0">(select class first)</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="fast-row">
                        <div class="fast-field">
                            <label>Gender</label>
                            <select name="fastGender" id="fastGender" class="form-control">
                                <option value="1">Boy</option>
                                <option value="0">Girl</option>
                                <option value="2">Other</option>
                            </select>
                        </div>
                        <div class="fast-field">
                            <label>Religion</label>
                            <select name="fastReligion" id="fastReligion" class="form-control">
                                <option value="">—</option>
                                <?php foreach ($religions as $r) {
                                    echo "<option value='{$r}'>{$r}</option>";
                                } ?>
                            </select>
                        </div>
                        <div class="fast-field">
                            <label>Blood Group</label>
                            <select name="fastBloodGrp" id="fastBloodGrp" class="form-control">
                                <option>N/A</option>
                                <option>A+</option><option>A-</option><option>B+</option><option>B-</option>
                                <option>AB+</option><option>AB-</option><option>O+</option><option>O-</option>
                            </select>
                        </div>
                        <div class="fast-field">
                            <label>Date of Birth</label>
                            <input type="date" name="fastDob" id="fastDob" class="form-control">
                        </div>
                    </div>

                    <div class="fast-row">
                        <div class="fast-field">
                            <label>Father's Name</label>
                            <input type="text" name="fastFather" id="fastFather" class="form-control" placeholder="Father's Name">
                        </div>
                        <div class="fast-field">
                            <label>Mother's Name</label>
                            <input type="text" name="fastMother" id="fastMother" class="form-control" placeholder="Mother's Name">
                        </div>
                        <div class="fast-field">
                            <label>Birth Reg No</label>
                            <input type="text" name="fastBirthRegNo" id="fastBirthRegNo" class="form-control" placeholder="Birth Reg No">
                        </div>
                        <div class="fast-field">
                            <label>Facilities</label>
                            <select name="fastFacilities" id="fastFacilities" class="form-control">
                                <option value="None">None</option>
                                <option value="Scholarship">Scholarship</option>
                                <option value="Full free">Full free</option>
                                <option value="Half free">Half free</option>
                            </select>
                        </div>
                    </div>

                    <div class="fast-row">
                        <div class="fast-field fast-field-wide">
                            <label>Present Address</label>
                            <input type="text" name="fastPresent" id="fastPresent" class="form-control" placeholder="Present Address">
                        </div>
                        <div class="fast-field">
                            <label>Phone</label>
                            <input type="text" name="fastPhone" id="fastPhone" class="form-control" placeholder="Phone">
                        </div>
                        <div class="fast-field">
                            <label>Emergency Phone</label>
                            <input type="text" name="fastEmergencyPhone" id="fastEmergencyPhone" class="form-control" placeholder="Emergency Phone">
                        </div>
                    </div>
                </div>
            </form>

            <!-- Navigation -->
            <div class="fast-nav">
                <button type="button" class="btn btn-default" id="fastPrevBtn">
                    <span class="dashicons dashicons-arrow-left-alt2"></span> Previous
                </button>
                <button type="button" class="btn btn-primary" id="fastNextBtn">
                    Next <span class="dashicons dashicons-arrow-right-alt2"></span>
                </button>
                <span class="text-muted fast-status" id="fastSaveStatus"></span>
            </div>
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
    var studentList = [];       // Array of student IDs in order
    var currentIndex = -1;      // 0-based index into studentList
    var saveInProgress = false;
    var hasUnsaved = false;
    var autoSaveTimer = null;

    /* ---------- AJAX: Load classes ---------- */
    function loadClasses(selectedVal) {
        $.post(siteUrl + '/inc/ajaxAction.php', { type: 'getClass' }, function(html) {
            $('#fastClass').html(html);
            if (selectedVal) $('#fastClass').val(selectedVal);
            onFastClassChange();
        });
    }

    /* ---------- AJAX: Load sections by class ---------- */
    function loadSections(classId, selectedVal) {
        var $sel = $('#fastSection');
        if (!classId) {
            $sel.html('<option value="0">— All Sections —</option>');
            return;
        }
        $.post(siteUrl + '/inc/ajaxAction.php', { type: 'getSection', class: classId }, function(html) {
            $sel.html('<option value="0">— All Sections —</option>');
            var clean = html.replace(/<option[^>]*>.*?<\/option>/, '');
            if (clean.trim()) $sel.append(clean);
            if (selectedVal) $sel.val(selectedVal);
        });
    }

    /* ---------- AJAX: Load years by class ---------- */
    function loadYears(classId, selectedVal) {
        var $sel = $('#fastYear');
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

    /* ---------- AJAX: Load groups for the form's Group dropdown ---------- */
    function loadGroupSelect(classId, selectedVal) {
        var $sel = $('#fastGroup');
        $sel.html('<option value="0">—</option>');
        if (!classId) return;
        $.post(siteUrl + '/inc/ajaxAction.php', { type: 'getGroupsByClass', class: classId }, function(html) {
            // Strip placeholder option
            var clean = html.replace(/<option[^>]*>.*?<\/option>/, '');
            if (clean.trim()) $sel.append(clean);
            if (selectedVal) $sel.val(selectedVal);
        });
    }

    /* ---------- Called when class changes ---------- */
    function onFastClassChange() {
        var cls = $('#fastClass').val();
        loadSections(cls, '');
        loadYears(cls, '');
        loadGroupSelect(cls, '');
    }

    $('#fastClass').on('change', onFastClassChange);

    /* ---------- Load student list (Go button) ---------- */
    function loadStudentList() {
        var cls = $('#fastClass').val();
        var yr  = $('#fastYear').val();
        var sec = $('#fastSection').val();

        if (!cls || !yr) {
            alert('Please select Class and Year.');
            return;
        }

        $('#fastHiddenClass').val(cls);
        $('#fastHiddenSection').val(sec);
        $('#fastHiddenYear').val(yr);

        $.post(siteUrl + '/inc/ajaxAction.php', {
            type: 'getFastStudentList',
            class: cls,
            section: sec,
            year: yr
        }, function(resp) {
            try {
                var data = JSON.parse(resp);
            } catch(e) {
                alert('Error loading student list.');
                return;
            }
            studentList = data.students || [];
            $('#fastTotalCount').text(studentList.length);
            $('#fastEntryPanel').show();

            if (studentList.length === 0) {
                // No students — start fresh with empty form
                currentIndex = -1;
                clearForm();
                $('#fastCurrentIdx').text('—');
                setStatus('No students found. Fill in a new student below.');
                // Enable form for new entry
                enableForm(true);
                return;
            }

            currentIndex = 0;
            loadStudent(currentIndex);
        });
    }

    $('#fastGoBtn').on('click', loadStudentList);

    /* ---------- New Entry button ---------- */
    $('#fastNewBtn').on('click', function() {
        // Make sure hidden fields are set from current filters
        $('#fastHiddenClass').val($('#fastClass').val());
        $('#fastHiddenSection').val($('#fastSection').val());
        $('#fastHiddenYear').val($('#fastYear').val());

        if (!$('#fastHiddenClass').val() || !$('#fastHiddenYear').val()) {
            alert('Please select Class and Year first.');
            return;
        }

        // Point past the end to trigger new entry mode
        currentIndex = studentList.length;
        clearForm();
        $('#fastStudentId').val('0');
        $('#fastInfoId').val('0');
        $('#fastCurrentIdx').text('New');
        $('#fastEntryPanel').show();
        enableForm(true);
        updateNavButtons();
        setStatus('New student — fill in details');
        $('#fastStdName').focus();
    });

    /* ---------- Load a single student by index ---------- */
    function loadStudent(index) {
        if (index < 0 || index >= studentList.length) return;

        currentIndex = index;
        $('#fastCurrentIdx').text(index + 1);
        setStatus('Loading…');
        enableForm(false);

        $.post(siteUrl + '/inc/ajaxAction.php', {
            type: 'getFastStudent',
            studentId: studentList[index]
        }, function(resp) {
            try {
                var data = JSON.parse(resp);
            } catch(e) {
                setStatus('Failed to load student data.');
                return;
            }
            if (data.error) {
                setStatus(data.error);
                return;
            }
            populateForm(data);
            enableForm(true);
            setStatus('');
            updateNavButtons();
        });
    }

    /* ---------- Populate form with student data ---------- */
    function populateForm(data) {
        $('#fastStudentId').val(data.studentid || 0);
        $('#fastInfoId').val(data.infoid || 0);
        $('#fastStdName').val(data.stdName || '');
        $('#fastStdNameBangla').val(data.stdNameBangla || '');
        $('#fastFacilities').val(data.facilities || 'None');
        $('#fastGender').val(data.stdGender != null ? data.stdGender : 1);
        $('#fastBirthRegNo').val(data.birth_reg_no || '');
        $('#fastDob').val(data.stdBrith || '');
        $('#fastBloodGrp').val(data.stdBldGrp || 'N/A');
        $('#fastReligion').val(data.stdReligion || '');
        $('#fastRoll').val(data.infoRoll || '');
        // Group dropdown — load options then set value
        loadGroupSelect($('#fastClass').val(), data.infoGroup || 0);
        $('#fastPresent').val(data.stdPresent || '');
        $('#fastFather').val(data.stdFather || '');
        $('#fastMother').val(data.stdMother || '');
        $('#fastPhone').val(data.stdPhone || '');
        $('#fastEmergencyPhone').val(data.stdEmergencyPhone || '');
        hasUnsaved = false;
    }

    /* ---------- Clear form for new entry ---------- */
    function clearForm() {
        $('#fastStudentId').val('0');
        $('#fastInfoId').val('0');
        $('#fastStdName').val('');
        $('#fastStdNameBangla').val('');
        $('#fastFacilities').val('None');
        $('#fastGender').val('1');
        $('#fastBirthRegNo').val('');
        $('#fastDob').val('');
        $('#fastBloodGrp').val('N/A');
        $('#fastReligion').val('');
        $('#fastRoll').val('');
        $('#fastPresent').val('');
        $('#fastFather').val('');
        $('#fastMother').val('');
        $('#fastPhone').val('');
        $('#fastEmergencyPhone').val('');
        hasUnsaved = false;
    }

    /* ---------- Enable / disable form during save ---------- */
    function enableForm(enabled) {
        $('#fastForm input, #fastForm select').prop('disabled', !enabled);
    }

    /* ---------- Update nav button states ---------- */
    function updateNavButtons() {
        var disablePrev = (currentIndex <= 0);
        var disableNext = (currentIndex >= studentList.length - 1);
        $('#fastPrevBtn').prop('disabled', disablePrev);
        $('#fastNextBtn').prop('disabled', disableNext);
    }

    /* ---------- Set status message ---------- */
    function setStatus(msg) {
        $('#fastSaveStatus').text(msg);
    }

    /* ---------- Auto-save current student ---------- */
    function autoSave(callback) {
        if (saveInProgress) {
            if (callback) setTimeout(function() { autoSave(callback); }, 300);
            return;
        }

        var sid = $('#fastStudentId').val();
        // If it's a new blank form (studentId=0 and name is empty), skip save
        if (sid == '0' && !$('#fastStdName').val().trim()) {
            hasUnsaved = false;
            if (callback) callback();
            return;
        }

        saveInProgress = true;
        setStatus('Saving…');

        $.post(siteUrl + '/inc/ajaxAction.php', {
            type: 'saveFastStudent',
            studentId: sid,
            infoId: $('#fastInfoId').val(),
            stdName: $('#fastStdName').val(),
            stdNameBangla: $('#fastStdNameBangla').val(),
            facilities: $('#fastFacilities').val(),
            stdGender: $('#fastGender').val(),
            birth_reg_no: $('#fastBirthRegNo').val(),
            stdBrith: $('#fastDob').val(),
            stdBldGrp: $('#fastBloodGrp').val(),
            stdReligion: $('#fastReligion').val(),
            stdRoll: $('#fastRoll').val(),
            stdGroup: $('#fastGroup').val(),
            stdPresent: $('#fastPresent').val(),
            stdFather: $('#fastFather').val(),
            stdMother: $('#fastMother').val(),
            stdPhone: $('#fastPhone').val(),
            stdEmergencyPhone: $('#fastEmergencyPhone').val(),
            class: $('#fastHiddenClass').val(),
            section: $('#fastHiddenSection').val(),
            year: $('#fastHiddenYear').val()
        }, function(resp) {
            saveInProgress = false;
            hasUnsaved = false;
            try {
                var data = JSON.parse(resp);
            } catch(e) {
                setStatus('Save failed.');
                if (callback) callback();
                return;
            }
            if (data.error) {
                setStatus('Error: ' + data.error);
                if (callback) callback();
                return;
            }
            // Update IDs for new inserts
            if (data.studentId) $('#fastStudentId').val(data.studentId);
            if (data.infoId) $('#fastInfoId').val(data.infoId);

            // If this was a newly inserted student, add to the list if not already there
            if (data.isNew && studentList.indexOf(parseInt(data.studentId)) === -1) {
                studentList.push(parseInt(data.studentId));
                $('#fastTotalCount').text(studentList.length);
            }

            setStatus('Saved ✓');
            if (callback) callback();
        }).fail(function() {
            saveInProgress = false;
            setStatus('Save failed (network).');
            if (callback) callback();
        });
    }

    /* ---------- Mark form as having unsaved changes ---------- */
    function markUnsaved() {
        hasUnsaved = true;
        setStatus('Unsaved changes…');
        // Debounced auto-save on field change (2 sec after last keystroke)
        clearTimeout(autoSaveTimer);
        autoSaveTimer = setTimeout(function() {
            if (hasUnsaved) autoSave();
        }, 2000);
    }

    $('#fastForm').on('change keyup', 'input, select', function() {
        // Ignore the group select being rebuilt
        if ($(this).attr('id') === 'fastGroup' && $(this).data('loading')) return;
        markUnsaved();
    });

    /* ---------- Navigation: Previous / Next ---------- */
    function goToPrev() {
        if (currentIndex <= 0) return;
        autoSave(function() {
            if (currentIndex > 0) {
                loadStudent(currentIndex - 1);
            }
        });
    }

    function goToNext() {
        if (currentIndex >= studentList.length - 1) {
            // At the end — add a new row
            autoSave(function() {
                currentIndex = studentList.length; // point beyond the end
                clearForm();
                $('#fastStudentId').val('0');
                $('#fastInfoId').val('0');
                $('#fastCurrentIdx').text('New');
                enableForm(true);
                updateNavButtons();
                setStatus('New student — fill in details');
            });
            return;
        }
        autoSave(function() {
            loadStudent(currentIndex + 1);
        });
    }

    $('#fastPrevBtn').on('click', goToPrev);
    $('#fastNextBtn').on('click', goToNext);

    /* ---------- Keyboard shortcuts ---------- */
    $(document).on('keydown', function(e) {
        // Only when the entry panel is visible
        if (!$('#fastEntryPanel').is(':visible')) return;
        // Don't intercept if typing in a textarea-like field (for prev/next)
        var tag = e.target.tagName;
        if (tag === 'TEXTAREA') return;

        if (e.altKey && e.key === 'ArrowLeft') {
            e.preventDefault();
            goToPrev();
        }
        if (e.altKey && e.key === 'ArrowRight') {
            e.preventDefault();
            goToNext();
        }
    });

    /* ---------- Also save when leaving the page ---------- */
    $(window).on('beforeunload', function() {
        if (hasUnsaved) {
            // Synchronous save attempt
            $.ajax({
                url: siteUrl + '/inc/ajaxAction.php',
                method: 'POST',
                async: false,
                data: buildSavePayload()
            });
        }
    });

    function buildSavePayload() {
        return {
            type: 'saveFastStudent',
            studentId: $('#fastStudentId').val(),
            infoId: $('#fastInfoId').val(),
            stdName: $('#fastStdName').val(),
            stdNameBangla: $('#fastStdNameBangla').val(),
            facilities: $('#fastFacilities').val(),
            stdGender: $('#fastGender').val(),
            birth_reg_no: $('#fastBirthRegNo').val(),
            stdBrith: $('#fastDob').val(),
            stdBldGrp: $('#fastBloodGrp').val(),
            stdReligion: $('#fastReligion').val(),
            stdRoll: $('#fastRoll').val(),
            stdGroup: $('#fastGroup').val(),
            stdPresent: $('#fastPresent').val(),
            stdFather: $('#fastFather').val(),
            stdMother: $('#fastMother').val(),
            stdPhone: $('#fastPhone').val(),
            stdEmergencyPhone: $('#fastEmergencyPhone').val(),
            class: $('#fastHiddenClass').val(),
            section: $('#fastHiddenSection').val(),
            year: $('#fastHiddenYear').val()
        };
    }

    /* ---------- Initial load on page ready ---------- */
    loadClasses('');

})(jQuery);
</script>

<style>
/* ---------- Modern form sections ---------- */
.fast-section {
    background: #fff;
    border: 1px solid #e0e4e8;
    border-radius: 8px;
    margin-bottom: 16px;
    overflow: hidden;
}
.fast-section-title {
    background: #f7f8fa;
    padding: 10px 16px;
    font-size: 14px;
    font-weight: 600;
    color: #2c3e50;
    border-bottom: 1px solid #e0e4e8;
    letter-spacing: 0.3px;
}
.fast-row {
    display: flex;
    flex-wrap: wrap;
    padding: 4px 16px 12px;
    gap: 0;
}
.fast-field {
    flex: 1 1 25%;
    min-width: 180px;
    padding: 8px 10px 0 0;
}
.fast-field-wide {
    flex: 1 1 50%;
    min-width: 280px;
}
.fast-field label {
    display: block;
    font-size: 13px;
    font-weight: 500;
    color: #555;
    margin-bottom: 3px;
    margin-top: 4px;
}
.fast-field .form-control {
    font-size: 14px;
    height: 36px;
    padding: 6px 10px;
    margin: 0;
    border: 1px solid #ccd0d4;
    border-radius: 4px;
    box-shadow: none;
    transition: border-color 0.15s ease;
}
.fast-field .form-control:focus {
    border-color: #2271b1;
    box-shadow: 0 0 0 1px #2271b1;
    outline: none;
}
.fast-field select.form-control {
    padding: 5px 8px;
}

/* ---------- Navigation bar ---------- */
.fast-nav {
    display: flex;
    justify-content: flex-end;
    align-items: center;
    gap: 10px;
    margin-top: 16px;
    padding-top: 16px;
    border-top: 1px solid #e0e4e8;
}
.fast-nav .btn {
    font-size: 14px;
    padding: 7px 18px;
}
.fast-status {
    font-size: 13px;
    font-style: italic;
    min-width: 120px;
    color: #666;
    margin-right: auto;
}

/* ---------- Panel body ---------- */
#fastEntryPanel .panel-body {
    padding: 20px;
}

/* ---------- Filter panel ---------- */
.panel-default .panel-heading {
    font-size: 14px;
}

/* ---------- Responsive ---------- */
@media (max-width: 768px) {
    .fast-field {
        flex: 1 1 100%;
        min-width: 100%;
    }
    .fast-field-wide {
        flex: 1 1 100%;
    }
}
</style>
