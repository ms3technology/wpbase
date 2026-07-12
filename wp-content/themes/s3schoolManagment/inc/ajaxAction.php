<?php

// 	define( 'SHORTINIT', true );
require( '../../../../wp-load.php' );
ob_start();

global $wpdb;

// Initialize default POST keys to prevent notices
if (!isset($_POST['type'])) $_POST['type'] = '';
if (!isset($_POST['action'])) $_POST['action'] = '';

require_once get_template_directory() . '/adminPages/functions/teacher-access.php';
$applyTeacherRestrictions = s3s_teacher_restrictions_enabled();

if($_POST['type'] == 'toggleTeacherRestrictions'){
	$enabled = $_POST['enabled'];
	$wpdb->replace(
		'sm_options',
		[
			'option_name'  => 's3s_teacher_restrictions_enabled',
			'option_value' => $enabled,
			'autoload'     => 'yes',
		],
		['%s', '%s', '%s']
	);
	echo "success";
	exit;
}

/*
	Check The Roll
*/
if($_POST['type'] == 'checkRoll'){
	$class 		= $_POST['class'];
	$section  = $_POST['section'];
	$year			= $_POST['year'];
	$roll			= $_POST['roll'];
	$std			= isset($_POST['std']) ? $_POST['std'] : '';

	$stdQuery = "SELECT `infoStdid` FROM `ct_studentinfo`
			WHERE `infoYear` = '$year' AND `infoClass` = $class AND `infoRoll` = $roll";

	if(isset($section) && !empty($section)){
		$stdQuery .= " AND `infoSection` = $section";
	}

	if($std != ''){
		$stdQuery .= " AND infoStdid != $std";
	}
	
	$student = $wpdb->get_results( $stdQuery );

	if(!empty($student)){
		echo 1;
	}else{
		echo 0;
	}
}


/*
	Get Optional and 4th sub Subject
*/
if($_POST['type'] == 'getOptionalSubject' || $_POST['type'] == 'getOpt4thSubjectByGroup'){
	$class = $_POST['class'];
	$group = ($_POST['group'] == '' || $_POST['group'] == 0) ? 'all' : $_POST['group'];

	$religionMap = [
		'Muslim'    => 1,
		'Hinduism'  => 2,
		'Buddist'   => 3,
		'Christian' => 4,
		'other'     => 5
	];

	$religionName = $_POST['stdReligion'] ?? '';
	$religionId = $religionMap[$religionName] ?? 0;

	$religionCondition = ($religionId > 0) 
		? "AND (religionId IS NULL OR religionId = 0 OR religionId = $religionId)"
		: "AND (religionId IS NULL OR religionId = 0)";

	$subjects = $wpdb->get_results("
		SELECT subjectid, subjectName 
		FROM ct_subject 
		WHERE subjectClass = '$class' 
		AND subOptinal = 1 
		AND (forGroup IN ('$group', 'all') OR forGroup LIKE '%\"$group\"%') 
		$religionCondition
		ORDER BY subjectName
	");

	// 	$subjects = $wpdb->get_results( "SELECT subjectid,subjectName FROM ct_subject WHERE subjectClass = '$class' AND subOptinal = 1 AND (forGroup IN ('$group', 'all') OR forGroup LIKE '%\"$group\"%') ORDER BY subjectName" );

	if(!empty($subjects)){
		echo "<label>Optional Subject(s)</label><br>";
	}

	foreach ($subjects as $subjct) {
		?>
		<label class="labelRadio">
			<input type="checkbox" name="stdOptionals[]" value="<?= $subjct->subjectid; ?>" checked> <?= $subjct->subjectName; ?>
		</label>
		<?php
	}

	$subjects4th = $wpdb->get_results( "SELECT subjectid,subjectName FROM ct_subject WHERE subjectClass = '$class' AND sub4th = 1 AND (forGroup IN ('$group', 'all') OR forGroup LIKE '%\"$group\"%') ORDER BY subjectName" );

	if(!empty($subjects4th)){
		echo "<br><br><label>4th Subject</label><br>";
	}

	$first = true;
	foreach ($subjects4th as $subjct) {
		?>
		<label class="labelRadio">
			<input type="checkbox" name="std4thsub[]" value="<?= $subjct->subjectid; ?>" <?= $first ? 'checked' : ''; ?>>
			<?= $subjct->subjectName; ?>
		</label>
		<?php
		$first = false;
	}
}



/*
	Get Section
*/
elseif($_POST['type'] == 'getSection'){

	$class = $_POST['class'];

	$current_user = wp_get_current_user();
	$sections_query = "SELECT sectionid,sectionName FROM ct_section WHERE forClass = '$class'";

	$sections_query .= " ORDER BY sectionName";

	$sections = $wpdb->get_results($sections_query);
	if(!empty($sections)){
		echo "<option value=''>Section</option>";
		foreach ($sections as $section) {
			?>
			<option value="<?= $section->sectionid ?>"><?= $section->sectionName ?></option>
			<?php
		}
	}else{
		echo "<option value=''>No sections available</option>";
	}
}

/*
	Get Groups by Class
*/
elseif($_POST['type'] == 'getGroupsByClass'){
	$class = $_POST['class'];
	
	$current_user = wp_get_current_user();
	$groups_query = "SELECT DISTINCT ct_group.groupId, ct_group.groupName 
		FROM ct_group 
		INNER JOIN ct_studentinfo ON ct_studentinfo.infoGroup = ct_group.groupId 
		WHERE ct_studentinfo.infoClass = '$class'";

	$groups_query .= " ORDER BY ct_group.groupName ASC";
	
	$groups = $wpdb->get_results($groups_query);
	
	echo "<option value=''>All Groups</option>";
	foreach ($groups as $group) {
		?>
		<option value="<?= $group->groupId ?>"><?= $group->groupName ?></option>
		<?php
	}
}



/*
	Get Exam
*/
elseif($_POST['type'] == 'getExams'){
	$class = $_POST['class'];
	$exams = $wpdb->get_results( "SELECT examid,examName FROM ct_exam WHERE examClass = '$class'" );
	if(empty($exams)){
		echo "<option value=''>No Exam for this Class</option>";
	}else{
		echo "<option value=''>Select An Exam</option>";
	}
	foreach ($exams as $exam) {
		?>
		<option value="<?= $exam->examid ?>"><?= $exam->examName ?></option>
		<?php
	}
}

/*
	Get active Exam for fee collecton
*/
elseif($_POST['type'] == 'getActiveExam'){
	$class = $_POST['class'];
	$exams = $wpdb->get_results( "SELECT examid FROM ct_exam WHERE examClass = '$class' AND active_for_collection=1" );
	if(empty($exams)){
		echo '';
	}else{
		echo $exams[0]->examid;
	}
}


/*
	Get Year
*/
elseif($_POST['type'] == 'getYears'){
	$class = $_POST['class'];
	$years = $wpdb->get_results( "SELECT infoYear FROM ct_studentinfo WHERE infoClass = $class GROUP BY infoYear ORDER BY infoYear ASC" );
	if(empty($years)){
		echo "<option value=''>No Student In this class</option>";
	}else{
		echo "<option value=''>Year</option>";
	}
	foreach ($years as $year) {
		?>
		<option value="<?= $year->infoYear ?>"><?= $year->infoYear ?></option>
		<?php
	}
}



/*
	Get Class
*/
elseif($_POST['type'] == 'getClass'){
	$classes = $wpdb->get_results("SELECT classid, className, havegroup FROM ct_class ORDER BY className");
	echo "<option value=''>— Select Class —</option>";
	foreach ($classes as $class) {
		?>
		<option value="<?= $class->classid ?>" data-havegroup="<?= $class->havegroup ?>"><?= $class->className ?></option>
		<?php
	}
}


/*
	Get Subject
*/
elseif($_POST['type'] == 'getSubject'){
	$class = $_POST['class'];

	$subjects = $wpdb->get_results( "SELECT subjectid,	subjectName FROM ct_subject WHERE subjectClass = '$class'" );
	if(empty($subjects)){
		echo "<option value=''>No subject!</option>";
	}else{
		echo "<option value=''>Select Subject</option>";
	}

	foreach ($subjects as $subject) {
		?>
		<option value="<?= $subject->subjectid ?>"><?= $subject->subjectName ?></option>
		<?php
	}
}

/*
	Get Subjects
*/
elseif($_POST['type'] == 'getSubjects'){
	$class1 = $_POST['class1'];
	$class2 = $_POST['class2'];

	$subjects = $wpdb->get_results( "SELECT subjectid, subjectName FROM ct_subject WHERE subjectClass IN ($class1,$class2)" );

	?>
	
		<ul class="list-unstyled list-inline">
			<?php
			foreach ($subjects as $key => $subject) {
				$num = $key+1;
				?>
					<li style="width: 49%">
						<label class="labelRadio">
							<input type="checkbox" name="subjects[]" value="<?= $subject->subjectid ?>"> <?= $subject->subjectName ?>
						</label>
					</li>
				<?php
			}
			?>
		</ul>
			
	<?php
}


/*
	Fast Entry: Get student list
*/
elseif($_POST['type'] == 'getFastStudentList'){
	$class 	 = intval($_POST['class']);
	$section = intval($_POST['section'] ?? 0);
	$year 	 = sanitize_text_field($_POST['year']);

	$where = "infoClass = $class AND infoYear = '$year'";
	if ($section > 0) $where .= " AND infoSection = $section";

	$students = $wpdb->get_results("SELECT infoStdid FROM ct_studentinfo WHERE $where ORDER BY infoRoll ASC");
	$ids = array();
	foreach ($students as $s) $ids[] = (int)$s->infoStdid;

	echo json_encode(['students' => $ids]);
}


/*
	Fast Entry: Get single student
*/
elseif($_POST['type'] == 'getFastStudent'){
	$studentId = intval($_POST['studentId']);

	$data = $wpdb->get_row($wpdb->prepare("
		SELECT ct_student.*, ct_studentinfo.*
		FROM ct_student
		LEFT JOIN ct_studentinfo ON ct_student.studentid = ct_studentinfo.infoStdid
		WHERE ct_student.studentid = %d
		LIMIT 1
	", $studentId));

	if (!$data) {
		echo json_encode(['error' => 'Student not found.']);
		exit;
	}

	echo json_encode($data);
}


/*
	Fast Entry: Save single student
*/
elseif($_POST['type'] == 'saveFastStudent'){
	$studentId  = intval($_POST['studentId'] ?? 0);
	$infoId     = intval($_POST['infoId'] ?? 0);
	$class      = intval($_POST['class'] ?? 0);
	$section    = intval($_POST['section'] ?? 0);
	$year       = sanitize_text_field($_POST['year'] ?? '');
	$group      = intval($_POST['stdGroup'] ?? 0);

	$roll       = sanitize_text_field($_POST['stdRoll'] ?? '');
	$stdName    = sanitize_text_field($_POST['stdName'] ?? '');
	$stdNameBn  = sanitize_text_field($_POST['stdNameBangla'] ?? '');
	$stdFather  = sanitize_text_field($_POST['stdFather'] ?? '');
	$stdMother  = sanitize_text_field($_POST['stdMother'] ?? '');
	$stdPhone   = sanitize_text_field($_POST['stdPhone'] ?? '');
	$stdEmerg   = sanitize_text_field($_POST['stdEmergencyPhone'] ?? '');
	$stdPresent = sanitize_textarea_field($_POST['stdPresent'] ?? '');
	$stdDob     = sanitize_text_field($_POST['stdBrith'] ?? '');
	$birthReg   = sanitize_text_field($_POST['birth_reg_no'] ?? '');
	$facilities = sanitize_text_field($_POST['facilities'] ?? 'None');
	$stdGender  = intval($_POST['stdGender'] ?? 1);
	$stdBldGrp  = sanitize_text_field($_POST['stdBldGrp'] ?? 'N/A');
	$stdReligion = sanitize_text_field($_POST['stdReligion'] ?? '');

	if (empty($stdName)) {
		echo json_encode(['error' => 'Student name is required.']);
		exit;
	}

	$isNew = ($studentId === 0);
	$currentUserId = get_current_user_id();

	$studentData = array(
		'stdName'         => $stdName,
		'stdNameBangla'   => $stdNameBn,
		'stdFather'       => $stdFather,
		'stdMother'       => $stdMother,
		'stdPhone'        => $stdPhone,
		'stdEmergencyPhone' => $stdEmerg,
		'stdPresent'      => $stdPresent,
		'stdBrith'        => $stdDob,
		'birth_reg_no'    => $birthReg,
		'facilities'      => $facilities,
		'stdGender'       => $stdGender,
		'stdBldGrp'       => $stdBldGrp,
		'stdReligion'     => $stdReligion,
		'stdCurntYear'    => $year,
		'stdCurrentClass' => $class,
	);

	if ($isNew) {
		// INSERT
		$studentData['stdAdmitYear']  = $year;
		$studentData['stdAdmitClass'] = $class;
		$studentData['stdPermanent']  = '';
		$studentData['stdNationality'] = 'Bangladeshi';
		$studentData['createdBy']     = $currentUserId;

		$inserted = $wpdb->insert('ct_student', $studentData);
		if (!$inserted) {
			echo json_encode(['error' => 'Failed to insert student.']);
			exit;
		}
		$studentId = $wpdb->insert_id;

		$wpdb->insert('ct_studentinfo', array(
			'infoStdid'    => $studentId,
			'infoClass'    => $class,
			'infoSection'  => $section,
			'infoGroup'    => $group,
			'infoRoll'     => $roll,
			'infoYear'     => $year,
			'infoOptionals' => 0,
			'info4thSub'   => 0,
		));
		$infoId = $wpdb->insert_id;

		echo json_encode([
			'studentId' => $studentId,
			'infoId'    => $infoId,
			'isNew'     => true,
			'message'   => 'Student created.'
		]);
	} else {
		// UPDATE
		$studentData['stdUpdatedAt'] = current_time('mysql');
		$wpdb->update('ct_student', $studentData, array('studentid' => $studentId));

		$infoData = array(
			'infoClass'   => $class,
			'infoSection' => $section,
			'infoGroup'   => $group,
			'infoRoll'    => $roll,
			'infoYear'    => $year,
			'info4thSub'  => 0,
		);

		if ($infoId > 0) {
			$wpdb->update('ct_studentinfo', $infoData, array('infoid' => $infoId));
		} else {
			$infoData['infoStdid'] = $studentId;
			$infoData['infoOptionals'] = 0;
			$wpdb->insert('ct_studentinfo', $infoData);
			$infoId = $wpdb->insert_id;
		}

		echo json_encode([
			'studentId' => $studentId,
			'infoId'    => $infoId,
			'isNew'     => false,
			'message'   => 'Student updated.'
		]);
	}
	exit;
}


/*
	Get Subject of exam
*/
elseif ($_POST['type'] == 'getExamSubject') {
$exam = intval($_POST['exam']); // Always sanitize
$group = isset($_POST['group']) ? $_POST['group'] : ''; // Get selected group
$subjects = []; // ✅ Initialize so foreach doesn't break later

$subs = $wpdb->get_results("SELECT examSubjects FROM ct_exam WHERE examid = $exam");

if (!empty($subs[0]->examSubjects)) {
	$subs = json_decode($subs[0]->examSubjects, true);
} else {
	$subs = [];
}

$current_user = wp_get_current_user();

if (!empty($subs)) {
	$subs_escaped = array_map('intval', $subs);
	$subjectQuery = "SELECT subjectid,subjectName FROM ct_subject 
			WHERE subjectid IN (" . implode(',', $subs_escaped) . ")";
	
	// Filter by group if selected
	if (!empty($group)) {
		$subjectQuery .= " AND (forGroup = 'all' OR forGroup = '$group' OR forGroup LIKE '%\"$group\"%')";
	}
	
	$subjectQuery .= " ORDER BY subjectName ASC";
	
	$subjects = $wpdb->get_results($subjectQuery);
}

if (empty($subjects)) {
	echo "<option value=''>No subject!</option>";
} else {
	echo "<option value=''>Select Subject</option>";
	foreach ($subjects as $subject) {
		echo '<option value="' . $subject->subjectid . '">' . $subject->subjectName . '</option>';
	}
}
}



/*
	Get All Student By Class
*/
elseif($_POST['type'] == 'getAllStudentByClass'){
	$class 		= $_POST['class'];
	$year 		= $_POST['year'];
	$section 	= $_POST['section'];
	?>
		<table class="table table-bordered table-responsive">
			<thead>
				<tr>
					<th>Name</th>
					<th>Group</th>
					<th>Roll</th>
					<th>Action</th>
				</tr>
			</thead>
			<tbody>
				<?php
				$url = str_replace("wp-content/themes/s3schoolManagment","", $_POST['siteUrl']);

				$students = $wpdb->get_results( "SELECT studentid,studentid,stdName,stdRoll,groupName FROM ct_student
					LEFT JOIN ct_group ON ct_student.stdGroup = ct_group.groupId 
					WHERE ct_student.stdAdmitClass = $class AND ct_student.stdCurntYear = '$year' AND ct_student.stdSection = $section AND ct_student.stdStatus = 1 ORDER BY ct_student.stdRoll ASC" );

				foreach ($students as $student) {
					?>
					<tr>
						<td><?= $student->stdName; ?></td>
						<td><?= $student->groupName; ?></td>
						<td><?= $student->stdRoll; ?></td>
						<td>

							<form class="pull-right actionForm" method="POST" action="">
						<input type="hidden" name="id" value="<?= $student->studentid; ?>">
						<a href="?page=student&option=view&id=<?= $student->studentid; ?>" class="btn-link">
							<span class="dashicons dashicons-visibility"></span></span>
						</a>

						<button type="submit" class="btn-link btnDelete" name="deleteStudent">
							<span class="dashicons dashicons-trash"></span>
						</button>
					</form>

					<!-- <form class="pull-right  actionForm" method="GET" action="">
						<input type="hidden" name="page" value="student">
						<input type="hidden" name="option" value="add">
						<input type="hidden" name="edit" value="<?= $student->studentid; ?>">
								<button type="submit" class="btn-link">

							<span class="dashicons dashicons-welcome-write-blog"></span></span>

						</button>
							</form> -->

						</td>
					</tr>
					<?php
				}
				?>
			</tbody>
		</table>
	<?php
}





/*
	Get Yeasr Or Section
*/
elseif($_POST['type'] == 'getYearSection'){
	$classid = $_POST['class'];

	$subs = $wpdb->get_results( "SELECT session FROM ct_class WHERE classid = $classid LIMIT 1" );
	$session = $subs[0]->session;

	$options = '';
    $currentYear = date("Y");
	if($session == 'year'){
		for ($i=-2; $i < 7; $i++) {
		     $sec = (date("Y")-$i);
            $selected = ($currentYear == $sec) ? 'selected' : '';
      
      $options .= "<option value='$sec' $selected>$sec</option>";
    } 
	}else{
	    $currentYear = date("Y")."-".(date("Y")+1);
		for ($i=-2; $i < 7; $i++) { 
      $sec = (date("Y")-($i+1))."-".(date("Y")-$i);
      $selected = ($currentYear == $sec) ? 'selected' : '';
      $options .= "<option value='$sec' $selected>$sec</option>";
    } 
	}

	echo $options;

}

/*
	Get Yeasr Or Section
*/
elseif($_POST['type'] == 'getStudentInfo'){
	
		$class 		= $_POST['class'];
		$section  = $_POST['section'];
		$year			= $_POST['year'];
		$roll			= $_POST['roll'];
		$group			= $_POST['group'];
		$admissionFeeSubHeadId	= $_POST['admissionFeeSubHeadId'];
		$admissionFormSubHeadId	= $_POST['admissionFormSubHeadId'];
		$monthlyFeeSubHeadId	= $_POST['monthlyFeeSubHeadId'];
		$transportFeeSubHeadId	= $_POST['transportFeeSubHeadId'];
		$coachingFeeSubHeadId	= $_POST['coachingFeeSubHeadId'];
		$registrationFeeSubHeadId	= $_POST['registrationFeeSubHeadId'];
		$fee_month			= $_POST['month'] == '' ? 0 : $_POST['month'];
		$std			= isset($_POST['std']) ? $_POST['std'] : '';
		// print_r($fee_month);exit;
	$studentId = 0;
	$stdQuery = "SELECT ct_studentinfo.infoStdid, ct_student.stdName, ct_student_wise_fee.transport_fee_id, ct_student_wise_fee.transport_type,ct_student_wise_fee.transport_required, ct_student.admission_type, ct_student.facilities FROM ct_studentinfo
	LEFT JOIN ct_student ON ct_student.studentid = ct_studentinfo.infoStdid
	LEFT JOIN ct_student_wise_fee ON ct_student.studentid = ct_student_wise_fee.student_id AND $class = ct_student_wise_fee.class_id AND '$year' = ct_student_wise_fee.year  AND 3 = ct_student_wise_fee.fee_type
	WHERE  ct_studentinfo.infoYear = '$year' AND ct_studentinfo.infoRoll = $roll AND ct_studentinfo.infoClass = $class";
	if(isset($section) && !empty($section)){
		$stdQuery .= " AND ct_studentinfo.infoSection = $section";
	}
	if(isset($group) && !empty($group)){
		$stdQuery .= " AND ct_studentinfo.infoGroup = $group";
	}
	$stdQuery .= " LIMIT 1";
	$result = [];

	
	 $studentInfo = $wpdb->get_results( $stdQuery );
	 if($studentInfo){
		$result['student_name']=$studentInfo[0]->stdName;
		$result['student_id']=$studentInfo[0]->infoStdid;
		$studentId  = $result['student_id'];
	 }else{
		$result['success'] = 'false';
	 }

	//  get active collection sub head id
	$subHeadId = $wpdb->get_results("SELECT * FROM ct_sub_head
	WHERE  active_for_collection = 1  AND relation_to = 1 and isHidden is null ORDER BY sub_head_name ASC");

	foreach($subHeadId as $val){
	//  NOTES: NEED TO SAVE DUE MONTH AND YEAR WISE
	// $fees = $wpdb->get_results("SELECT fee FROM ct_student_fee_list WHERE sub_head_id = $val->id AND class_id = $GLOBALS[class] AND year = $GLOBALS[year]");
	$feesQuery = "SELECT fee FROM ct_student_fee_list WHERE sub_head_id = $val->id AND class_id = $class AND year = '$year' ";

	// No need section for fee
	// if(isset($section) && !empty($section)){
	// 	$feesQuery .= " AND section = $section";
	// }

	if(isset($group) && !empty($group)){
		$feesQuery .= " AND group_id = $group";
	}
	$feesQuery .= "  ORDER BY id DESC";
	$fees = $wpdb->get_results($feesQuery);
	

	if($fees){
		$fees = $fees[0]->fee;
	}else{
		$fees = 0;
	}
	if($val->type == 1){
		// monthly
		$sumOfFees = 0;
		$fee_month_list = [];
		for($i = $fee_month; $i>=1; $i--){
			$feeInfoQuery = "SELECT fee FROM ct_student_monthly_fee_summary WHERE sub_head_id = $val->id AND class_id = $class AND year = '$year' AND month = $i AND student_id = $studentId";
			if(isset($section) && !empty($section)){
				$feeInfoQuery .= " AND section = $section";
			}
			if(isset($group) && !empty($group)){
				$feeInfoQuery .= " AND group_id = $group";
			}
			
			$feeInfo = $wpdb->get_results($feeInfoQuery);
			if(!$feeInfo){
				if($val->id == $monthlyFeeSubHeadId){
					if($studentInfo[0]->facilities == 'Full free' || $studentInfo[0]->facilities == 'Scholarship'){
						// $sumOfFees += 0;
					} else if($studentInfo[0]->facilities == 'Half free' ){
						$sumOfFees += ($fees/2);
					}else{
						// check student wise monthly fee
						
						$checkfees = "SELECT monthly_fee FROM ct_student WHERE studentid = $studentId";
						
						$studentwisefees = $wpdb->get_results($checkfees);
						if($studentwisefees[0]->monthly_fee > 0){						
							$fees = $studentwisefees[0]->monthly_fee;
						}
						$sumOfFees += $fees;
						
					}
				}else if($val->id == $transportFeeSubHeadId){
					if($studentInfo[0]->transport_required == 1){
						
						
						$transport_fee_id = $studentInfo[0]->transport_fee_id;
						
						$feesquery = "SELECT amount FROM ct_transport_fee_list WHERE id = $transport_fee_id";
						$fees = $wpdb->get_results($feesquery);
						if($fees){
							
							$fees = $fees[0]->amount;
							
							if($studentInfo[0]->transport_type == 1){ 
								// one way
								$fees = $fees/2;
							}
						}else{
							$fees = 0;
						}
						$sumOfFees += $fees;
					}else{
						$sumOfFees += 0;
					}
				}else if($val->id == $coachingFeeSubHeadId){
					$checkfees = "SELECT amount FROM ct_student_wise_fee WHERE fee_type = 1 AND student_id = $studentId  AND class_id = $class AND year = '$year'";
					if(isset($section) && !empty($section)){
						$checkfees .= " AND section = $section";
					}
					if(isset($group) && !empty($group)){
						$checkfees .= " AND group_id = $group";
					}
					$studentwisefees = $wpdb->get_results($checkfees);
					if($studentwisefees && $studentwisefees[0]->amount > 0){						
						$fees = $studentwisefees[0]->amount;
					}else{
						$fees = 0;
					}
					$sumOfFees += $fees;
					
				}else{
					$sumOfFees += $fees;
				}
				$fee_month_list[] = $i;
			}
		}
		// print_r($fee_month_list);exit;
		$result['month_list'] = $fee_month_list;
		$result['subheadid'.$val->id] = $sumOfFees;
	}else if($val->type == 2){
		// yearly 
		
		$feeInfoQuery = "SELECT fee FROM ct_student_yearly_fee_summary WHERE sub_head_id = $val->id AND class_id = $class AND year = '$year' AND student_id = $studentId";
		if(isset($section) && !empty($section)){
			$feeInfoQuery .= " AND section = $section";
		}
		if(isset($group) && !empty($group)){
			$feeInfoQuery .= " AND group_id = $group";
		}
		$feeInfo = $wpdb->get_results($feeInfoQuery);
			if(!$feeInfo){
				// check admission fee for new or promoted student
				if( $val->id == $admissionFeeSubHeadId){
					if($studentInfo[0]->admission_type == 1){
					    if($studentInfo[0]->facilities == 'Half free' ){
						    $fees = ($fees/2);
    					}else{
    						$fees = $fees;
    					}
						$result['subheadid'.$val->id] = $fees;
					}else{
						$feesquery = "SELECT amount FROM ct_admission_fee_promoted WHERE class = $class";
						$fees = $wpdb->get_results($feesquery);
						if($fees){	
						    $fees = $fees[0]->amount;
						    if($studentInfo[0]->facilities == 'Half free' ){
						        $fees = ($fees/2);
        					}
							
						}else{
							$fees = 0;
						}
						$result['subheadid'.$val->id] = $fees;
					}
			    }else if( $val->id == $admissionFormSubHeadId){
					    if($studentInfo[0]->facilities == 'Half free' ){
						    $fees = ($fees/2);
    					}else{
    						$fees = $fees;
    					}
						$result['subheadid'.$val->id] = $fees;
				}else if($val->id == $registrationFeeSubHeadId){
					$checkfees = "SELECT amount FROM ct_student_wise_fee WHERE fee_type = 2 AND student_id = $studentId  AND class_id = $class AND year = '$year'";
					if(isset($section) && !empty($section)){
						$checkfees .= " AND section = $section";
					}
					if(isset($group) && !empty($group)){
						$checkfees .= " AND group_id = $group";
					}
					$studentwisefees = $wpdb->get_results($checkfees);
					if($studentwisefees && $studentwisefees[0]->amount > 0){						
						$fees = $studentwisefees[0]->amount;
					}else{
					   // if($studentInfo[0]->facilities == 'Half free' ){
						  //  $fees = ($fees/2);
    				// 	}else{
    				// 		$fees = $fees;
    				// 	}
    				$fees = $fees;
					}
					$result['subheadid'.$val->id] = $fees;
				}else{
					$result['subheadid'.$val->id] = $fees;
				}
				
			}
	}else if($val->type == 3){
		// exam
		// get active exam id
		$activeExamId = $wpdb->get_results("SELECT examid FROM ct_exam WHERE active_for_collection = 1 AND examClass = $class LIMIT 1");
		if($activeExamId){
			$activeExamId = $activeExamId[0]->examid;
			$feeInfoQuery = "SELECT fee FROM ct_student_exam_fee_summary WHERE sub_head_id = $val->id AND class_id = $class AND exam_id = $activeExamId AND year = '$year' AND student_id = $studentId";
			if(isset($section) && !empty($section)){
				$feeInfoQuery .= " AND section = $section";
			}
			if(isset($group) && !empty($group)){
				$feeInfoQuery .= " AND group_id = $group";
			}
			$feeInfo = $wpdb->get_results($feeInfoQuery);

			if(!$feeInfo){
				$result['subheadid'.$val->id] = $fees;
			}	
		}else{
			$result['subheadid'.$val->id] = 0;//exam fee o
		}
		
	}else if($val->type == 4){
		// OTHER
		$result['subheadid'.$val->id] = $fees;				
	}
	// echo '<pre>'; print_r($result);exit;
	// print_r( $fees[0]->fee);exit;
	// $fees =  getDefaultFee($val->id,$class, $year, $group );

 }
	
	


	 echo json_encode( $result);
	//  echo  $studentInfo[0]->stdName;
	// return json_encode( $studentInfo);

}
elseif($_POST['type'] == 'getStudentFeeAmount'){
		$class 		= $_POST['class'];
		$year			= $_POST['year'];
		$group			= $_POST['group'];
	
	$result = [];

	
	
	//  get active collection sub head id
	$subHeadId = $wpdb->get_results("SELECT * FROM ct_sub_head
	WHERE  relation_to = 1 and isHidden is null ORDER BY sub_head_name ASC");
	// echo '<pre>';
	// print_r($subHeadId);exit;
	foreach($subHeadId as $val){
		 
	//  NOTES: NEED TO SAVE DUE MONTH AND YEAR WISE
	// $fees = $wpdb->get_results("SELECT fee FROM ct_student_fee_list WHERE sub_head_id = $val->id AND class_id = $GLOBALS[class] AND year = $GLOBALS[year]");
	$feesQuery = "SELECT fee FROM ct_student_fee_list WHERE sub_head_id = $val->id AND class_id = $class AND year = '$year' ";

	// No need section for fee
	// if(isset($section) && !empty($section)){
	// 	$feesQuery .= " AND section = $section";
	// }

	if(isset($group) && !empty($group)){
		$feesQuery .= " AND group_id = $group";
	}
	$feesQuery .= "  ORDER BY id DESC";
	$fees = $wpdb->get_results($feesQuery);
	if($fees){
		$fees = $fees[0]->fee;
	}else{
		$fees = 0;
	}
		// OTHER
		$result['subheadid'.$val->id] = $fees;				
	
	// echo '<pre>'; print_r($result);exit;
	// print_r( $fees[0]->fee);exit;
	// $fees =  getDefaultFee($val->id,$class, $year, $group );

 }
	
	


	 echo json_encode( $result);
	//  echo  $studentInfo[0]->stdName;
	// return json_encode( $studentInfo);

}elseif($_POST['type'] == 'hasGroup'){
		$class = $_POST['class'];
		
		$classInfo = $wpdb->get_results( "SELECT havegroup FROM ct_class WHERE classid = '$class' " );

		if (!empty($classInfo) && $classInfo[0]->havegroup == 1) {
            echo "true";
        } else {
            echo "false";
        }

	/*
		Get Optional and 4th sub Subject
	*/
}


/*
	Get Exam Subjects as JSON (for schedule page)
*/
elseif ($_POST['type'] == 'getExamSubjectsJson') {
	$exam = intval($_POST['exam']);
	$subjects = array();

	$subs_raw = $wpdb->get_var($wpdb->prepare("SELECT examSubjects FROM ct_exam WHERE examid = %d", $exam));
	if (!empty($subs_raw)) {
		$subs = json_decode($subs_raw, true);
	} else {
		$subs = array();
	}

	if (!empty($subs) && is_array($subs)) {
		$subs_escaped = array_map('intval', $subs);
		$placeholders = implode(',', $subs_escaped);
		$subjects = $wpdb->get_results(
			"SELECT subjectid, subjectName FROM ct_subject WHERE subjectid IN (" . $placeholders . ") ORDER BY subid"
		);
	}

	header('Content-Type: application/json');
	echo json_encode($subjects);
}


/*
	Load existing exam schedule for class/exam/year
*/
elseif ($_POST['type'] == 'getExamSchedule') {
	$classid = intval($_POST['classid']);
	$examid  = intval($_POST['examid']);
	$year    = isset($_POST['year']) ? trim($_POST['year']) : '';

	$row = $wpdb->get_row($wpdb->prepare(
		"SELECT subject_dates FROM ct_exam_schedule WHERE classid = %d AND examid = %d AND year = %s LIMIT 1",
		$classid, $examid, $year
	));

	header('Content-Type: application/json');
	if ($row && !empty($row->subject_dates)) {
		// subject_dates already stored as JSON string
		echo $row->subject_dates;
	} else {
		echo json_encode(new stdClass());
	}
}


/*
	Save exam schedule (insert or update)
	Expects: classid, examid, year, subject_dates (JSON string or object)
*/
elseif ($_POST['type'] == 'saveExamSchedule') {
	$classid = intval($_POST['classid']);
	$examid  = intval($_POST['examid']);
	$year    = isset($_POST['year']) ? trim($_POST['year']) : '';
	$subject_dates_raw = isset($_POST['subject_dates']) ? wp_unslash($_POST['subject_dates']) : '';

	header('Content-Type: application/json');

	if ($classid <= 0 || $examid <= 0 || $year === '') {
		echo json_encode(array('success' => false, 'message' => 'Missing required fields.'));
		exit;
	}

	// Normalize subject_dates to JSON string
	if (is_string($subject_dates_raw)) {
		$subject_dates_decoded = json_decode($subject_dates_raw, true);
	} else {
		$subject_dates_decoded = $subject_dates_raw;
	}

	if (!is_array($subject_dates_decoded)) {
		$subject_dates_decoded = array();
	}

	// sanitize subject ids and dates
	$clean = array();
	foreach ($subject_dates_decoded as $sid => $dt) {
		$sid_i = intval($sid);
		$dt_s = trim($dt);
		// accept empty dates as empty string, otherwise try to normalize YYYY-MM-DD
		if ($dt_s !== '') {
			// basic validation YYYY-MM-DD
			if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $dt_s)) {
				// try to convert common formats to YYYY-MM-DD
				$ts = strtotime($dt_s);
				if ($ts !== false) {
					$dt_s = date('Y-m-d', $ts);
				} else {
					// invalid date, skip
					$dt_s = '';
				}
			}
		}
		if ($sid_i > 0) {
			$clean[$sid_i] = $dt_s;
		}
	}

	$subject_dates_json = json_encode($clean);
	$now = current_time('mysql');

	// check for existing record
	$existing_id = $wpdb->get_var($wpdb->prepare(
		"SELECT scheduleid FROM ct_exam_schedule WHERE classid = %d AND examid = %d AND year = %s LIMIT 1",
		$classid, $examid, $year
	));

	if ($existing_id) {
		$updated = $wpdb->update(
			'ct_exam_schedule',
			array(
				'subject_dates' => $subject_dates_json,
				'updated_at' => $now
			),
			array('scheduleid' => $existing_id),
			array('%s', '%s'),
			array('%d')
		);

		if ($updated !== false) {
			echo json_encode(array('success' => true, 'message' => 'Schedule updated.'));
		} else {
			echo json_encode(array('success' => false, 'message' => 'Update failed.'));
		}
	} else {
		$inserted = $wpdb->insert(
			'ct_exam_schedule',
			array(
				'classid' => $classid,
				'examid' => $examid,
				'year' => $year,
				'subject_dates' => $subject_dates_json,
				'created_at' => $now,
				'updated_at' => $now
			),
			array('%d', '%d', '%s', '%s', '%s', '%s')
		);

		if ($inserted) {
			echo json_encode(array('success' => true, 'message' => 'Schedule saved.'));
		} else {
			echo json_encode(array('success' => false, 'message' => 'Insert failed.'));
		}
	}
}

/*
	Update Student Phone (inline editing)
*/
else if ($_POST['type'] == 'updateStudentPhone') {
        $student_id = intval($_POST['student_id']);
        $field      = isset($_POST['field']) ? sanitize_text_field($_POST['field']) : 'stdPhone';
        $phone      = isset($_POST['phone']) ? sanitize_text_field($_POST['phone']) : '';

        if ($student_id <= 0) {
                header('Content-Type: application/json');
                echo json_encode(array('success' => false, 'data' => 'Invalid student ID.'));
                exit;
        }

        // Only allow valid column names
        if (!in_array($field, array('stdPhone', 'stdEmergencyPhone'))) {
                header('Content-Type: application/json');
                echo json_encode(array('success' => false, 'data' => 'Invalid field.'));
                exit;
        }

        $updated = $wpdb->update(
                'ct_student',
                array($field => $phone),
                array('studentid' => $student_id),
                array('%s'),
                array('%d')
        );

        header('Content-Type: application/json');
        if ($updated !== false) {
                echo json_encode(array('success' => true, 'data' => array('phone' => $phone)));
        } else {
                echo json_encode(array('success' => false, 'data' => 'Database update failed.'));
        }
}


/*
        Update Student Group (inline editing)
*/
else if ($_POST['type'] == 'updateStudentGroup') {
        $infoid   = intval($_POST['infoid']);
        $group_id = intval($_POST['group_id']);

        if ($infoid <= 0) {
                header('Content-Type: application/json');
                echo json_encode(array('success' => false, 'data' => 'Invalid enrollment ID.'));
                exit;
        }

        $updated = $wpdb->update(
                'ct_studentinfo',
                array('infoGroup' => $group_id),
                array('infoid' => $infoid),
                array('%d'),
                array('%d')
        );

        // Get the group name for the response
        $group_name = '';
        if ($group_id > 0) {
                $group_row = $wpdb->get_row($wpdb->prepare("SELECT groupName FROM ct_group WHERE groupId = %d", $group_id));
                if ($group_row) {
                        $group_name = $group_row->groupName;
                }
        }

        header('Content-Type: application/json');
        if ($updated !== false) {
                echo json_encode(array('success' => true, 'data' => array('group_name' => $group_name, 'group_id' => $group_id)));
        } else {
                echo json_encode(array('success' => false, 'data' => 'Database update failed.'));
        }
}


/*
        Sync Optional Subjects — finds rows in ct_studentinfo with empty infoOptionals
        and fills them with optional subject IDs matching the class and group.
*/
else if ($_POST['type'] == 'syncOptionalSubjects') {
        $class_id = intval($_POST['class_id']);

        if ($class_id <= 0) {
                header('Content-Type: application/json');
                echo json_encode(array('success' => false, 'data' => 'Invalid class.'));
                exit;
        }

        // 1. Fetch all groups for this class (same join as getGroupsByClass)
        $groups = $wpdb->get_results($wpdb->prepare(
                "SELECT DISTINCT ct_group.groupId, ct_group.groupName
                 FROM ct_group
                 INNER JOIN ct_studentinfo ON ct_studentinfo.infoGroup = ct_group.groupId
                 WHERE ct_studentinfo.infoClass = %d
                 ORDER BY ct_group.groupName ASC",
                $class_id
        ));

        if (empty($groups)) {
                header('Content-Type: application/json');
                echo json_encode(array('success' => false, 'data' => 'No groups found for this class.'));
                exit;
        }

        $total_updated = 0;
        $results = array();

        // 2. Loop through each group one by one
        foreach ($groups as $group) {
                $group_id = intval($group->groupId);
                $group_name = $group->groupName;

                // Get optional subject IDs for this class and group
                $optional_subjects = $wpdb->get_col($wpdb->prepare(
                        "SELECT subjectId FROM ct_subject
                         WHERE subOptinal = 1
                           AND subjectClass = %d
                           AND (forGroup = 'all' OR forGroup = %d OR forGroup LIKE %s)",
                        $class_id,
                        $group_id,
                        '%"' . $group_id . '"%'
                ));

                // Build JSON array of string IDs
                $json_ids = '[]';
                if (!empty($optional_subjects)) {
                        $str_ids = array_map('strval', $optional_subjects);
                        $json_ids = json_encode($str_ids);
                }

                // Update rows that have empty infoOptionals
                $updated = $wpdb->query($wpdb->prepare(
                        "UPDATE ct_studentinfo
                         SET infoOptionals = %s
                         WHERE infoClass = %d
                           AND infoGroup = %d
                           AND (infoOptionals IS NULL OR infoOptionals = '' OR infoOptionals = '0')",
                        $json_ids,
                        $class_id,
                        $group_id
                ));

                if ($updated !== false && $updated > 0) {
                        $total_updated += $updated;
                        $results[] = sprintf(
                                '<strong>%s</strong>: %d student(s) updated (%d optional subject(s))',
                                esc_html($group_name),
                                $updated,
                                count($optional_subjects)
                        );
                } else {
                        $results[] = sprintf(
                                '<strong>%s</strong>: No students needed update',
                                esc_html($group_name)
                        );
                }
        }

        header('Content-Type: application/json');
        echo json_encode(array(
                'success' => true,
                'data' => array(
                        'summary' => sprintf('Sync complete. <strong>%d</strong> total student(s) updated across %d group(s).', $total_updated, count($groups)),
                        'results' => $results
                )
        ));
}


/*
        Update Student Subjects (inline modal editing) — updates infoOptionals and info4thSub
*/
else if ($_POST['type'] == 'updateStudentSubjects') {
        $infoid   = intval($_POST['infoid']);
        $class_id = intval($_POST['class_id']);

        if ($infoid <= 0 || $class_id <= 0) {
                header('Content-Type: application/json');
                echo json_encode(array('success' => false, 'data' => 'Invalid parameters.'));
                exit;
        }

        $optionals = isset($_POST['optionals']) ? json_decode(stripslashes($_POST['optionals']), true) : [];
        $fourth    = isset($_POST['fourth']) ? json_decode(stripslashes($_POST['fourth']), true) : [];

        if (!is_array($optionals)) $optionals = [];
        if (!is_array($fourth)) $fourth = [];

        // Store as JSON string arrays (or 0 if empty)
        $opt_json   = !empty($optionals) ? json_encode(array_values($optionals)) : '0';
        $fourth_json = !empty($fourth) ? json_encode(array_values($fourth)) : '0';

        $updated = $wpdb->update(
                'ct_studentinfo',
                array(
                        'infoOptionals' => $opt_json,
                        'info4thSub'    => $fourth_json
                ),
                array('infoid' => $infoid),
                array('%s', '%s'),
                array('%d')
        );

        // Build HTML for the updated cell display
        $all_ids = array_merge($optionals, $fourth);
        $html = '';
        if (!empty($all_ids)) {
                $ids_str = implode(',', array_map('intval', $all_ids));
                $subjects = $wpdb->get_results(
                        "SELECT subjectid, subjectName FROM ct_subject WHERE subjectid IN ($ids_str) ORDER BY subjectName"
                );
                foreach ($subjects as $subj) {
                        $class = in_array((string)$subj->subjectid, $fourth) ? 'frtSub' : 'optSub';
                        $html .= '<span data-id="'.$subj->subjectid.'" class="'.$class.'">'.$subj->subjectName.'</span> ';
                }
        }

        header('Content-Type: application/json');
        if ($updated !== false) {
                echo json_encode(array('success' => true, 'data' => array('html' => $html)));
        } else {
                echo json_encode(array('success' => false, 'data' => 'Database update failed.'));
        }
}