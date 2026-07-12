<script src="https://unpkg.com/jspdf@latest/dist/jspdf.umd.min.js">
</script><!-- <script src="https://unpkg.com/jspdf-autotable"></script> -->
<script src="	https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.6/jspdf.plugin.autotable.min.js"></script>


<style>
.std-img-upload-container {
    position: relative;
    width: 50px;
    height: 50px;
    cursor: pointer;
    border: 1px solid #ddd;
    border-radius: 4px;
    overflow: hidden;
    background: #f9f9f9;
    display: flex;
    align-items: center;
    justify-content: center;
}
.std-img-upload-container img {
    max-width: 100%;
    max-height: 100%;
    object-fit: cover;
}
.std-img-upload-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.4);
    display: none;
    align-items: center;
    justify-content: center;
    color: #fff;
}
.std-img-upload-container:hover .std-img-upload-overlay {
    display: flex;
}
.std-img-upload-overlay span {
    font-size: 20px;
}
.std-img-uploading {
    opacity: 0.5;
    pointer-events: none;
}
/* Spinner for uploading state */
.std-img-uploading::after {
    content: "";
    position: absolute;
    width: 20px;
    height: 20px;
    border: 3px solid #ccc;
    border-top-color: #333;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}
@keyframes spin {
    to { transform: rotate(360deg); }
}

/* Editable phone cell */
.editable-phone-cell {
    cursor: pointer;
    position: relative;
}
.editable-phone-cell .editable-phone {
    cursor: pointer;
    padding: 2px 4px;
    border-radius: 3px;
}
.editable-phone-cell .editable-phone:hover {
    background-color: #e8f4ff;
}
.editable-phone .phone-display-empty {
    color: #999;
    font-style: italic;
    font-size: 11px;
}
.editable-phone .phone-display-empty::before {
    content: "[+] Click to add";
}
.editable-phone .phone-input {
    display: none;
}

/* Editable subjects cell */
.editable-subjects-cell {
    cursor: pointer;
}
.editable-subjects-cell:hover {
    background-color: #f0f8ff;
}

/* Editable group cell */
.editable-group {
    cursor: pointer;
    position: relative;
}
.editable-group .group-display-empty {
    color: #999;
    font-style: italic;
}
.editable-group .group-display-empty::before {
    content: "[+] Click to set";
}
.editable-group:hover {
    background-color: #f0f8ff;
}
.editable-group .group-select {
    display: none;
}
</style>


<?php
global $wpdb,$s3sRedux;
  $yearGroup = $wpdb->get_results( "SELECT stdCurntYear FROM ct_student GROUP BY stdCurntYear" );
  $classGroup = $wpdb->get_results( "SELECT classid,className FROM ct_student
    LEFT JOIN ct_class ON ct_class.classid = ct_student.stdAdmitClass
    GROUP BY stdAdmitClass" );

  $admitYear = isset($_POST['filter']) ? $_POST['filter'] : date("Y");
  ?>
  <div class="panel panel-info">
    <div class="panel-heading">
      <?php $class =  (isset($_POST['stdclass'])) ? $_POST['stdclass'] : '' ?> 
      <?php $year =  (isset($_POST['stdyear'])) ? $_POST['stdyear'] : '' ?> 
      <h3>
        Students <?= (isset($_POST['stdyear'])) ? '('.$clsName.', '.$year.' )' : '' ?> <br>
        <small>Search For Students</small>
      </h3>
    </div>
    <div class="panel-body">
      <div class="panel-group stdView">
        <form action="" method="GET" class="form-inline">
          <input type="hidden" name="page" value="student">
          <div class="form-group">
            <label>Class</label>
            <select id='resultClass' class="form-control" name="stdclass" required>
              <?php

                $classQuery = $wpdb->get_results( "SELECT classid,className FROM ct_class WHERE classid IN (SELECT infoClass FROM ct_studentinfo GROUP BY infoClass ORDER BY className ASC)" );
                echo "<option value=''>Select Class</option>";

                foreach ($classQuery as $class) {
                  echo "<option value='".$class->classid."'>".$class->className."</option>";
                }
              ?>
            </select>
          </div>

          <div class="form-group ">
            <label>Section</label>
            <select id="resultSection" class="form-control" name="sec" disabled>
              <option disabled selected>Select Class First</option>
            </select>
          </div>

          <div class="form-group ">
            <label>Group</label>
            <select id="resultGroup" class="form-control" name="group">
              <option value="">Select Group</option>
              <?php
                $groups = $wpdb->get_results("SELECT * FROM ct_group");
                foreach ($groups as $groups) {
                  $selected = ($edit->infoGroup == $groups->groupId) ? 'selected' : '';
                  ?>
                  <option value='<?= $groups->groupId ?>' <?= $selected ?>>
                    <?= $groups->groupName ?>
                  </option>
                  <?php
                }
              ?>
            </select>
          </div>

          <div class="form-group">
            <label>Year</label>
            <select id='resultYear' class="form-control" name="stdyear" required disabled>
              <option disabled selected>Select Class First</option>
            </select>
          </div>


          <div class="form-group">
            <input class="form-control btn-success" type="submit" value="Go">
          </div>
        </form>
        <?php

        if(isset($_GET['stdyear'])){ ?>
          <?php 
            $class  = $_GET['stdclass'];
            $year   = $_GET['stdyear'];
            $sec    = isset($_GET['sec'])   ? $_GET['sec']   : '';
            $group  = isset($_GET['group']) ? $_GET['group'] : '';

            $stSql = "SELECT studentid,stdName,stdReligion,stdFather,stdMother,infoRoll,sectionName,infoOptionals,info4thSub,stdPhone,stdEmergencyPhone,groupName,stdImg,className,stdPresent,stdGender,stdAdmitYear,infoid,infoGroup FROM ct_student
              LEFT JOIN ct_studentinfo ON ct_student.studentid = ct_studentinfo.infoStdid
              LEFT JOIN ct_group ON ct_studentinfo.infoGroup = ct_group.groupId
              LEFT JOIN ct_section ON ct_studentinfo.infoSection = ct_section.sectionid
              LEFT JOIN ct_class ON ct_class.classid = $class
              WHERE infoClass = $class AND infoYear = '$year'";

            if ($sec != '') { $stSql .= " AND infoSection = $sec"; }
            if ($group != '') { $stSql .= " AND infoGroup = $group"; }

            $stSql .= " ORDER BY sectionid,infoRoll ASC";

            $students = $wpdb->get_results( $stSql );
            $totalstd = sizeof($students);

            $statSql = "SELECT 
              SUM(CASE WHEN stdGender = 1 THEN 1 ELSE 0 END) AS totalBoys,
              SUM(CASE WHEN stdGender = 0 THEN 1 ELSE 0 END) AS totalGirls,
              COUNT(*) AS total
              FROM ct_student
              LEFT JOIN ct_studentinfo ON ct_student.studentid = ct_studentinfo.infoStdid
              WHERE infoClass = $class AND infoYear = '$year'";
            $statistics = $wpdb->get_row( $statSql );
          ?>
          <div class="text-right">
            <button onclick="fnExcelReport()">Download Excel</button>
            <button id="pdfBtn" onclick="exportPDF()">Download PDF</button>
          </div>
          <br>

         <div style="overflow-x: auto; -webkit-overflow-scrolling: touch;">
            <div style="text-align: center; position: relative; min-width: 800px;">
              <img height="80px" style="position: absolute;left: 10px;top: 10px" src="<?= $s3sRedux['instLogo']['url'] ?>">
              <h2 style="margin: 5px 0 5px 0;"><b><?= $s3sRedux['institute_name'] ?></b></h2>
              <p style="color:#2b5591; font-size: 14px; margin: 0;"><?= $s3sRedux['institute_address'] ?></p>
              <h3>Student List (<?= $totalstd ?>)</h3>
            </div>

            <table class="table table-bordered table-responsive">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>ID NO:</th>
                  <th>Name</th>
                  <th>Group ✏️</th>
                  <th style="line-height: 1"><small>Class - Section</small></th>
                  <th><span class="frtSub">4th</span> & <span class="optSub">Optional</span> Subject</th>
                  <th>Phones ✏️</th>
                  <th>Gender & Religion</th>
                  <th>Address</th>
                  <th>Image</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>

                <?php
                foreach ($students as $key => $student) {
                  $stdGender = 'Boy';
                  if ($student->stdGender == 0) {
                    $stdGender = 'Girl';
                  }elseif ($student->stdGender == 2) {
                    $stdGender = 'Other';
                  }
                  $otrSubj = array();
                  $opt = $student->infoOptionals;
                  $frth = $student->info4thSub;

                  if (!empty($opt)) {
                    $otrSubj = json_decode($opt);
                    $otrSubj[] = $frth;
                  }else{
                    $otrSubj[] = $frth;
                  }
                  if(sizeof($otrSubj) > 0){
                    $subSql = "SELECT subjectid,subjectName FROM ct_subject WHERE subjectid IN (".implode(", ", $otrSubj).")";
                    $optSubjs = $wpdb->get_results( $subSql );
                  }
                  ?>
                  <tr>
                    <td><?= ($s3sRedux['stdidpref'] == 'year') ? $student->stdAdmitYear: $s3sRedux['stdidpref']; ?><?= sprintf("%05s", ($student->studentid + $s3sRedux['stdid'] )) ?></td>
                    <td><?= $student->infoRoll; ?></td>
                    <td><?= $student->stdName; ?><br><small>Father: <?= $student->stdFather; ?></small></td>
                    <?php $allGroups = $wpdb->get_results("SELECT * FROM ct_group ORDER BY groupName"); ?>
                    <td class="editable-group" data-infoid="<?= $student->infoid ?>" data-std-id="<?= $student->studentid ?>">
                        <span class="group-display <?= empty($student->groupName) ? 'group-display-empty' : '' ?>"><?= !empty($student->groupName) ? $student->groupName : '' ?></span>
                        <select class="group-select form-control input-sm" style="width:130px;height:24px;padding:0 4px;font-size:12px;">
                            <option value="">None</option>
                            <?php foreach ($allGroups as $g) {
                                $sel = ($g->groupId == $student->infoGroup) ? 'selected' : '';
                                echo '<option value="'.$g->groupId.'" '.$sel.'>'.$g->groupName.'</option>';
                            } ?>
                        </select>
                    </td>
                    <td><?= $student->className; ?><br>Sec - <?= $student->sectionName; ?></td>
                    <td class="editable-subjects-cell"
                        data-infoid="<?= $student->infoid ?>"
                        data-class="<?= $class ?>"
                        data-group="<?= $student->infoGroup ?>"
                        data-optionals='<?= !empty($student->infoOptionals) && $student->infoOptionals != '0' ? htmlspecialchars($student->infoOptionals, ENT_QUOTES) : '[]' ?>'
                        data-4th='<?php
                            $data4thVal = '[]';
                            if (!empty($student->info4thSub) && $student->info4thSub != '0') {
                                $decoded4th = json_decode($student->info4thSub);
                                if (is_array($decoded4th)) {
                                    $data4thVal = json_encode($decoded4th);
                                } else {
                                    $data4thVal = json_encode([$student->info4thSub]);
                                }
                            }
                            echo htmlspecialchars($data4thVal, ENT_QUOTES);
                        ?>'>
                      <?php

                      if(sizeof($otrSubj) > 0){
                        foreach ($optSubjs as $subj) {
                          $ofclss = ($frth == $subj->subjectid) ? 'frtSub' : "optSub";
                          echo '<span data-id="'.$subj->subjectid.'" class="'.$ofclss.'">'.$subj->subjectName.'</span>';
                          
                        }
                      }
                      ?>
                    </td>
                    <td class="editable-phone-cell" data-std-id="<?= $student->studentid ?>" style="min-width:160px;">
                        <div class="editable-phone" data-field="stdPhone">
                            <span class="phone-display <?= empty($student->stdPhone) ? 'phone-display-empty' : '' ?>"><?= !empty($student->stdPhone) ? $student->stdPhone : '' ?></span>
                            <input type="text" class="phone-input form-control input-sm" value="<?= $student->stdPhone; ?>" style="width:130px;height:24px;padding:2px 6px;font-size:12px;">
                        </div>
                        <div class="editable-phone" data-field="stdEmergencyPhone">
                            <span class="phone-display <?= empty($student->stdEmergencyPhone) ? 'phone-display-empty' : '' ?>"><?= !empty($student->stdEmergencyPhone) ? $student->stdEmergencyPhone : '' ?></span>
                            <input type="text" class="phone-input form-control input-sm" value="<?= $student->stdEmergencyPhone; ?>" style="width:130px;height:24px;padding:2px 6px;font-size:12px;">
                        </div>
                    </td>
                    <td><?= $stdGender; ?> <?= $student->stdReligion ?></td>
                    <td><?= $student->stdPresent; ?></td>
                   
                    <td>
                      <div class="std-img-upload-container" data-id="<?= $student->studentid ?>">
                          <img src="<?= !empty($student->stdImg) ? $student->stdImg : get_template_directory_uri() . '/img/image.png' ?>" class="std-img-preview">
                          <div class="std-img-upload-overlay">
                              <span class="dashicons dashicons-upload"></span>
                          </div>
                          <input type="file" class="std-img-input" style="display:none;" accept="image/*">
                      </div>
                    </td>
                    <td>

                      <form class="pull-right actionForm" method="POST" action="">

                        <a href="?page=student&option=view&id=<?= $student->studentid; ?>&class=<?= $class ?>&syear=<?= $year ?>" class="btn-link">
                          <span class="dashicons dashicons-visibility"></span></span>
                        </a>

                        <a href="?page=student&option=add&edit=<?= $student->studentid; ?>&class=<?= $class ?>" class="btn-link">
                          <span class="dashicons dashicons-welcome-write-blog"></span></span>
                        </a>

                        <button type="button" class="btn-link btnDelete" name="deleteStudent" data-id='<?= $student->studentid ?>'>
                          <span class="dashicons dashicons-trash"></span>
                        </button>

                      </form>
                    </td>
                  </tr>

                  <?php
                }
                ?>
              </tbody>
            </table>
          </div>
          </div>

          <!-- For Export -->
         <div id="dtudentsTblDiv" class="hidden">
           <style>
            .pdf-container { width: 100%; font-family: sans-serif; }
            .header-table { width: 100%; margin-bottom: 20px; margin-top: 10px;}
            .header-text { text-align: center; vertical-align: middle; }
            .inst-name { font-size: 16pt; font-weight: bold; margin: 0; color: #000; }
            .inst-addr { font-size: 11pt; color: #555; margin: 5px 0; }
            .report-title { font-size: 12pt; font-weight: bold; margin: 10px 0; color: #2b5591; }
            
            .info-table { width: 100%; margin-bottom: 10px; border-collapse: collapse; }
            .info-table td { padding: 8px; font-size: 10pt; border: 1px solid #ddd; background: #f9f9f9; }
            
            .students-table { width: 100%; border-collapse: collapse; font-size: 9pt; table-layout: fixed; }
            .students-table th { background-color: #4472C4; color: white; padding: 10px 5px; border: 1px solid #335a96; text-align: left; }
            .students-table td { padding: 8px 5px; border: 1px solid #ddd; vertical-align: top; word-wrap: break-word; }
            .students-table tr:nth-child(even) { background-color: #f9f9f9; }
            
            .bangla-text { font-family: 'kalpurush', sans-serif; font-size: 12pt; color: #333; }
            .label-text { font-weight: bold; color: #555; font-size: 8pt; display: inline-block; width: 15px; }
          </style>

          <?php
          $sectionName = '';
          if ($sec != '' && $sec != 'all') {
            $section = $wpdb->get_row("SELECT sectionName FROM ct_section WHERE sectionid = $sec");
            if ($section) {
              $sectionName = $section->sectionName;
            }
          }
          ?>

          <div class="pdf-container">
            <table class="header-table">
              <tr>
                <td class="header-text">
                  <div class="inst-name"><?= $s3sRedux['institute_name'] ?></div>
                  <div class="inst-addr"><?= $s3sRedux['institute_address'] ?></div>
                  <div class="report-title" style="border:none">Class: <?= $students[0]->className ?> <?= $sec ? 'Section: ' . $sectionName : '' ?></div>
                </td>
              </tr>
            </table>

            <table class="info-table">
              <tr>
                <td><strong>Boy:</strong> <?= $statistics->totalBoys ?></td>
                <td><strong>Girl:</strong> <?= $statistics->totalGirls ?></td>
                <td><strong>Total Students:</strong> <?= $statistics->total ?></td>
              </tr>
            </table>

            <table id="studentsTbl" class="students-table">
              <thead>
                <tr>
                  <th style="width: 40px;">Roll No</th>
                  <th>Student Name</th>
                  <th>Father's Name</th>
                  <th>Mother's Name</th>
                  <th>Date of Birth</th>
                  <th>Birth Registration No</th>
                  <th>Gender & Religion</th>
                  <th>Contact &<br> Emergency Phone</th>
                  <th>Address</th>
                </tr>
              </thead>
              <tbody>
                <?php
                foreach ($students as $key => $student) {
                  $stdGender = 'Boy';
                  if ($student->stdGender == 0) {
                    $stdGender = 'Girl';
                  } elseif ($student->stdGender == 2) {
                    $stdGender = 'Other';
                  }
                ?>
                  <tr>
                    <td style="text-align: center;"><?= $student->infoRoll; ?></td>
                    <td>
                      <?= $student->stdName; ?><br>
                      <span class="bangla-text"><?= $student->stdNameBangla; ?></span>
                    </td>
                    <td>
                      <span class="label-text"></span> <?= $student->stdFather; ?><br>
                      <span class="bangla-text"><?= $student->stdFatherBangla ?></span><br>
                    </td>
                    <td>
                      <span class="label-text"></span> <?= $student->stdMother; ?><br>
                      <span class="bangla-text"><?= $student->stdMotherBangla ?></span>
                    </td>
                    <td>
                      <?= $student->stdBrith; ?>
                    </td>
                    <td>
                      <?= esc_html($student->birth_reg_no); ?>
                    </td>
                    <td><?= $stdGender; ?> <br> <?= $student->stdReligion ?></td>
                    <td>
                      <?= $student->stdPhone; ?> <br>
                      <?= !empty($student->stdEmergencyPhone) ? $student->stdEmergencyPhone : ''; ?>
                    </td>
                    <td><?= $student->stdPresent; ?></td>
                  </tr>
                <?php
                }
                ?>
              </tbody>
            </table>
          </div>
        </div>
        <?php } ?>
      </div>
    </div>
  </div>


<!-- <script src="https://unpkg.com/jspdf"></script> -->


<!-- Subjects Edit Modal -->
<div class="modal fade" id="subjectsEditModal" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Edit 4th &amp; Optional Subjects</h4>
      </div>
      <div class="modal-body">
        <div class="form-group">
          <label>Group</label>
          <select id="modalSubjectGroup" class="form-control">
            <option value="">Select Group</option>
            <?php
            $allGroupsModal = $wpdb->get_results("SELECT * FROM ct_group ORDER BY groupName");
            foreach ($allGroupsModal as $g) {
              echo '<option value="'.$g->groupId.'">'.$g->groupName.'</option>';
            }
            ?>
          </select>
        </div>
        <hr>
        <div id="modalSubjectsContainer">
          <p class="text-muted">Select a group to load subjects...</p>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary" id="modalSubjectsSave">Save</button>
      </div>
    </div>
  </div>
</div>
<!-- End Subjects Edit Modal -->

<script type="text/javascript">
  (function($) {
    $('#resultClass').change(function() {
      var $siteUrl = '<?= get_template_directory_uri() ?>';

      $.ajax({
        url: $siteUrl+"/inc/ajaxAction.php",
        method: "POST",
        data: { class : $(this).val(), type : 'getYears' },
        dataType: "html"
      }).done(function( msg ) {
        $( "#resultYear" ).html( msg );
        $( "#resultYear" ).prop('disabled', false);
      });

      $.ajax({
        url: $siteUrl+"/inc/ajaxAction.php",
        method: "POST",
        data: { class : $(this).val(), type : 'getSection' },
        dataType: "html"
      }).done(function( msg ) {
        $( "#resultSection" ).html( msg );
        $( "#resultSection" ).prop('disabled', false);
      });
    });

    
    // Student Image Upload
    $(document).on('click', '.std-img-upload-container', function(e) {
        if (e.target.classList.contains('std-img-input')) {
            return;
        }
        $(this).find('.std-img-input').click();
    });

    $(document).on('change', '.std-img-input', function(e) {
        var file = e.target.files[0];
        if (!file) return;

        var container = $(this).closest('.std-img-upload-container');
        var studentId = container.data('id');
        var preview = container.find('.std-img-preview');
        var formData = new FormData();

        formData.append('student_image', file);
        formData.append('student_id', studentId);
        formData.append('type', 'uploadStudentImage');

        container.addClass('std-img-uploading');

        $.ajax({
            url: window.location.href, // Send to current page
            type: 'POST',
            data: formData,
            dataType: 'json',
            contentType: false,
            processData: false,
            success: function(response) {
                container.removeClass('std-img-uploading');
                if (response.success) {
                    preview.attr('src', response.data.url);
                } else {
                    alert('Error: ' + response.data);
                }
            },
            error: function() {
                container.removeClass('std-img-uploading');
                alert('Upload failed. Please try again.');
            }
        });
    });

    // ===== Inline Editable Phone Number (two fields: stdPhone + stdEmergencyPhone) =====
    // Click on a phone row to switch to edit mode
    $(document).on('click', '.editable-phone-cell .editable-phone', function(e) {
        if ($(e.target).is('.phone-input')) return;
        var $row = $(this);
        $row.find('.phone-display').hide();
        $row.find('.phone-input').show().focus().select();
    });

    // Save phone on Enter or blur, cancel on Escape
    $(document).on('keydown', '.editable-phone-cell .phone-input', function(e) {
        if (e.keyCode === 13) {
            e.preventDefault();
            $(this).trigger('blur');
        } else if (e.keyCode === 27) {
            var $row = $(this).closest('.editable-phone');
            var origVal = $row.find('.phone-display').text().trim();
            $(this).val(origVal);
            $(this).hide();
            $row.find('.phone-display').show();
        }
    });

    $(document).on('blur', '.editable-phone-cell .phone-input', function() {
        var $input = $(this);
        var $row = $input.closest('.editable-phone');
        var $cell = $row.closest('.editable-phone-cell');
        var studentId = $cell.data('std-id');
        var field = $row.data('field');
        var newPhone = $input.val().trim();
        var oldPhone = $row.find('.phone-display').text().trim();

        if (newPhone === oldPhone) {
            $input.hide();
            $row.find('.phone-display').show();
            return;
        }

        $input.prop('disabled', true);

        var $siteUrl = '<?= get_template_directory_uri() ?>';

        $.ajax({
            url: $siteUrl + '/inc/ajaxAction.php',
            method: 'POST',
            data: {
                type: 'updateStudentPhone',
                student_id: studentId,
                field: field,
                phone: newPhone
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    var $display = $row.find('.phone-display');
                    var savedPhone = response.data.phone;
                    $display.text(savedPhone);
                    if (savedPhone === '') {
                        $display.addClass('phone-display-empty');
                    } else {
                        $display.removeClass('phone-display-empty');
                    }
                } else {
                    alert('Failed to update ' + field + ': ' + response.data);
                    $input.val(oldPhone);
                }
            },
            error: function() {
                alert('Network error. ' + field + ' not updated.');
                $input.val(oldPhone);
            },
            complete: function() {
                $input.prop('disabled', false).hide();
                $row.find('.phone-display').show();
            }
        });
    });

    // ===== Inline Editable Group =====
    // Click on the cell to switch to edit mode
    $(document).on('click', '.editable-group', function(e) {
        if ($(e.target).is('.group-select')) return;
        var $td = $(this);
        // Store original value for cancel
        if (!$td.data('orig-group')) {
            $td.data('orig-group', $td.find('.group-select').val());
        }
        $td.find('.group-display').hide();
        $td.find('.group-select').show().focus();
    });

    // Save group on change
    $(document).on('change', '.editable-group .group-select', function() {
        $(this).trigger('blur');
    });

    // Cancel on Escape
    $(document).on('keydown', '.editable-group .group-select', function(e) {
        if (e.keyCode === 27) {
            var $td = $(this).closest('.editable-group');
            var origVal = $td.data('orig-group') || '';
            $(this).val(origVal);
            $(this).hide();
            $td.find('.group-display').show();
        }
    });

    // Auto-save on blur
    $(document).on('blur', '.editable-group .group-select', function() {
        var $select = $(this);
        var $td = $select.closest('.editable-group');
        var infoId = $td.data('infoid');
        var newGroupId = $select.val();
        var oldGroupId = $td.data('orig-group') || '';

        if (newGroupId === oldGroupId) {
            $select.hide();
            $td.find('.group-display').show();
            return;
        }

        $select.prop('disabled', true);
        var $siteUrl = '<?= get_template_directory_uri() ?>';

        $.ajax({
            url: $siteUrl + '/inc/ajaxAction.php',
            method: 'POST',
            data: {
                type: 'updateStudentGroup',
                infoid: infoId,
                group_id: newGroupId
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    var $display = $td.find('.group-display');
                    var newName = response.data.group_name;
                    var isActiveGroup = response.data.group_id > 0;
                    $display.text(newName);
                    if (isActiveGroup) {
                        $display.removeClass('group-display-empty');
                    } else {
                        $display.addClass('group-display-empty');
                    }
                    $td.data('orig-group', newGroupId);
                } else {
                    alert('Failed to update group: ' + response.data);
                    $select.val(oldGroupId);
                }
            },
            error: function() {
                alert('Network error. Group not updated.');
                $select.val(oldGroupId);
            },
            complete: function() {
                $select.prop('disabled', false).hide();
                $td.find('.group-display').show();
            }
        });
    });

    // ===== 4th & Optional Subjects Edit Modal =====
    var $subjectsModal = $('#subjectsEditModal');
    var $currentSubjectsCell = null;

    /* Click on subjects cell — open modal */
    $(document).on('click', '.editable-subjects-cell', function() {
        $currentSubjectsCell = $(this);
        var infoid = $(this).data('infoid');
        var classId = $(this).data('class');
        var groupId = $(this).data('group');
        var optionalsData = $(this).attr('data-optionals') || '[]';
        var fourthData = $(this).attr('data-4th') || '[]';

        // Store on modal for later use
        $subjectsModal.data('cell-infoid', infoid);
        $subjectsModal.data('cell-class', classId);
        $subjectsModal.data('cell-optionals', optionalsData);
        $subjectsModal.data('cell-4th', fourthData);

        $('#modalSubjectGroup').val(groupId);
        loadModalSubjects(classId, groupId, optionalsData, fourthData);

        $subjectsModal.modal('show');
    });

    /* Group change — reload subjects */
    $('#modalSubjectGroup').change(function() {
        var classId = $subjectsModal.data('cell-class');
        var groupId = $(this).val();
        var optionalsData = $subjectsModal.data('cell-optionals');
        var fourthData = $subjectsModal.data('cell-4th');
        loadModalSubjects(classId, groupId, optionalsData, fourthData);
    });

    function loadModalSubjects(classId, groupId, optionalsData, fourthData) {
        var $container = $('#modalSubjectsContainer');
        if (!groupId) {
            $container.html('<p class="text-muted">Select a group to load subjects...</p>');
            return;
        }
        $container.html('<p class="text-muted">Loading...</p>');

        var $siteUrl = '<?= get_template_directory_uri() ?>';
        $.ajax({
            url: $siteUrl + '/inc/ajaxAction.php',
            method: 'POST',
            data: {
                type: 'getOpt4thSubjectByGroup',
                class: classId,
                group: groupId
            },
            dataType: 'html'
        }).done(function(html) {
            $container.html(html);

            // Apply saved selections
            try {
                var savedOpts = JSON.parse(optionalsData || '[]');
                var saved4th = JSON.parse(fourthData || '[]');

                // Uncheck all optional, then check saved ones
                $container.find('input[name="stdOptionals[]"]').prop('checked', false);
                if (savedOpts.length) {
                    savedOpts.forEach(function(id) {
                        $container.find('input[name="stdOptionals[]"][value="' + id + '"]').prop('checked', true);
                    });
                }

                // Uncheck all 4th, then check saved ones
                $container.find('input[name="std4thsub[]"]').prop('checked', false);
                if (saved4th.length) {
                    saved4th.forEach(function(id) {
                        $container.find('input[name="std4thsub[]"][value="' + id + '"]').prop('checked', true);
                    });
                }
            } catch(e) {
                console.log('Error applying saved subjects:', e);
            }
        });
    }

    /* Save button */
    $('#modalSubjectsSave').click(function() {
        var infoid = $subjectsModal.data('cell-infoid');
        var classId = $subjectsModal.data('cell-class');
        var $btn = $(this);

        var optionals = [];
        $('#modalSubjectsContainer input[name="stdOptionals[]"]:checked').each(function() {
            optionals.push($(this).val());
        });

        var fourth = [];
        $('#modalSubjectsContainer input[name="std4thsub[]"]:checked').each(function() {
            fourth.push($(this).val());
        });

        $btn.prop('disabled', true).html('Saving...');

        var $siteUrl = '<?= get_template_directory_uri() ?>';
        $.ajax({
            url: $siteUrl + '/inc/ajaxAction.php',
            method: 'POST',
            data: {
                type: 'updateStudentSubjects',
                infoid: infoid,
                class_id: classId,
                optionals: JSON.stringify(optionals),
                fourth: JSON.stringify(fourth)
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    if ($currentSubjectsCell) {
                        $currentSubjectsCell.attr('data-optionals', JSON.stringify(optionals));
                        $currentSubjectsCell.attr('data-4th', JSON.stringify(fourth));
                        $currentSubjectsCell.html(response.data.html);
                    }
                    $subjectsModal.modal('hide');
                } else {
                    alert('Error: ' + response.data);
                }
            },
            error: function() {
                alert('Network error. Please try again.');
            },
            complete: function() {
                $btn.prop('disabled', false).html('Save');
            }
        });
    });
  })( jQuery );


  /*=====================Excel Export*/

  function fnExcelReport(){
    var tab_text="<table border='2px'><tr bgcolor='#87AFC6'>";
    var textRange; var j=0;
    tab = document.getElementById('studentsTbl'); // id of table

    for(j = 0 ; j < tab.rows.length ; j++){     
      tab_text=tab_text+tab.rows[j].innerHTML+"</tr>";
    }

    tab_text=tab_text+"</table>";
    tab_text= tab_text.replace(/<A[^>]*>|<\/A>/g, "");
    
    tab_text= tab_text.replace(/<input[^>]*>|<\/input>/gi, ""); 

    var ua = window.navigator.userAgent;
    var msie = ua.indexOf("MSIE "); 

    if (msie > 0 || !!navigator.userAgent.match(/Trident.*rv\:11\./)){
      txtArea1.document.open("txt/html","replace");
      txtArea1.document.write(tab_text);
      txtArea1.document.close();
      txtArea1.focus(); 
      sa=txtArea1.document.execCommand("SaveAs",true,"students.xls");
    }  
    else                 //other browser not tested on IE 11
      sa = window.open('data:application/vnd.ms-excel,' + encodeURIComponent(tab_text));  

    return (sa);
  }


  function exportPDF() {
    var btn = document.getElementById('pdfBtn');
    var originalText = btn.innerHTML;
    btn.disabled = true;
    var dotCount = 0;
    var loadingInterval = setInterval(function() {
      dotCount = (dotCount + 1) % 4;
      btn.innerHTML = 'Downloading' + '.'.repeat(dotCount);
    }, 400);

    // Get the HTML of the table
    var tableDiv = document.getElementById('dtudentsTblDiv');
    if (!tableDiv) {
      alert('Table not found for export.');
      return;
    }
    var html = tableDiv.innerHTML;

    // Prepare the payload
    var data = {
      html: html,
      filename: 'students.pdf',
      format: 'A4',
      orientation: 'L',
      font: 'sans-serif'
    };

    // Create a form data object
    var formData = new FormData();
    for (var key in data) {
      if (data.hasOwnProperty(key)) {
        formData.append(key, data[key]);
      }
    }

    // Send the request to the API
    fetch('https://cloud.barnomala.com/api/v1/download-pdf', {
      method: 'POST',
      body: formData
    })
      .then(function(response) {
        if (!response.ok) throw new Error('PDF generation failed');
        return response.blob();
      })
      .then(function(blob) {
        // Create a link to download the PDF
        var url = window.URL.createObjectURL(blob);
        var a = document.createElement('a');
        a.href = url;
        a.download = data.filename;
        document.body.appendChild(a);
        a.click();
        setTimeout(function() {
          window.URL.revokeObjectURL(url);
          document.body.removeChild(a);
        }, 100);
      })
      .catch(function(error) {
        alert('PDF export failed: ' + error.message);
      })
      .finally(function() {
        clearInterval(loadingInterval);
        btn.innerHTML = originalText;
        btn.disabled = false;
      });
  }
</script>
