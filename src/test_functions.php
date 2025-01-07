<?PHP

// run: rm test.log;  php test_functions.php | less


include 'functions.php';

global $debugging;
$debugging = true;

global $debug_log;
$debug_log = "test.log";

// test function fix_year to make sure it returns the correct year
// for dates from 2024-12-20 to 2025-01-19

function test_range_fix_year ($period_type, $period, $period_year, $start_date, $expected_year) {

	$test_date = new DateTime($start_date);
	for ($i = 0; $i < 31; $i++) {
		$r = fix_year($period_type, $period, $period_year, $test_date);
		echo $period . "-" . $period_year . " -> " . $r . " (expected: " . $expected_year . ")";
		if ($r == $expected_year) {
			echo " OK\n";
		} else {
			echo " ERROR\n";
		}
		// add a day to the test date
		$test_date->add(new DateInterval('P1D'));
	}
	echo "~~~~ +1 Y \n";

	// next year
	$period_year = $period_year + 1;

	$test_date = new DateTime($start_date);
	for ($i = 0; $i < 31; $i++) {
		$r = fix_year($period_type, $period, $period_year, $test_date);
		echo $period . "-" . $period_year . " -> " . $r . " (expected: " . $expected_year . ")";
		if ($r == $expected_year) {
			echo " OK\n";
		} else {
			echo " ERROR\n";
		}
		// add a day to the test date
		$test_date->add(new DateInterval('P1D'));
	}
	echo "\n";

}

function test_fix_year () {

	// W01-2025 starts on 2024-12-29

	test_range_fix_year("W", "52", "2024", "2024-12-28 00:00:00", "2024");
	test_range_fix_year("W", "52", "2024", "2025-01-05 00:00:00", "2024");

	test_range_fix_year("W", "01", "2025", "2024-12-29 00:00:00", "2025");
	test_range_fix_year("W", "01", "2025", "2024-12-31 00:00:00", "2025");
	test_range_fix_year("W", "01", "2025", "2025-01-01 00:00:00", "2025");
	test_range_fix_year("W", "01", "2025", "2025-01-05 00:00:00", "2025");

	test_range_fix_year("M", "12", "2024", "2024-12-20 00:00:00", "2024");
	test_range_fix_year("M", "12", "2024", "2025-01-05 00:00:00", "2024");

	// W52-2025 starts on 2025-12-21
	// W53-2025 starts on 2025-12-28 and ends on 2026-01-03
	// W01-2026 starts on 2026-01-04

	test_range_fix_year("W", "52", "2025", "2025-12-21 00:00:00", "2025");
	test_range_fix_year("W", "52", "2025", "2026-01-05 00:00:00", "2025");

	test_range_fix_year("W", "53", "2025", "2025-12-29 00:00:00", "2025");
	test_range_fix_year("W", "53", "2025", "2026-01-03 00:00:00", "2025");
	test_range_fix_year("W", "53", "2025", "2026-01-05 00:00:00", "2025");

	test_range_fix_year("W", "01", "2025", "2025-12-29 00:00:00", "2026");
	test_range_fix_year("W", "01", "2025", "2026-01-03 00:00:00", "2026");
	test_range_fix_year("W", "01", "2025", "2026-01-05 00:00:00", "2026");

}

function test_bulk_fix_year () {

$test_date = new DateTime('2024-12-20 00:00:00');
for ($i = 0; $i < 31; $i++) {
	$r = fix_year("M", "12", "2024", $test_date);
	echo "M12-2024 -> " . $r . "\n";
	// add a day to the test date
	$test_date->add(new DateInterval('P1D'));
}
echo "\n";

$test_date = new DateTime('2024-12-20 00:00:00');
for ($i = 0; $i < 31; $i++) {
	$r = fix_year("M", "01", "2025", $test_date);
	echo "M01-2025 -> " . $r . "\n";
	// add a day to the test date
	$test_date->add(new DateInterval('P1D'));
}
echo "\n";

$test_date = new DateTime('2024-12-20 00:00:00');
for ($i = 0; $i < 31; $i++) {
	$r = fix_year("M", "12", "2025", $test_date);
	echo "M12-2025 -> " . $r . "\n";
	// add a day to the test date
	$test_date->add(new DateInterval('P1D'));
}
echo "\n";

$test_date = new DateTime('2024-12-20 00:00:00');
for ($i = 0; $i < 31; $i++) {
	$r = fix_year("M", "01", "2024", $test_date);
	echo "M01-2024 -> " . $r . "\n";
	// add a day to the test date
	$test_date->add(new DateInterval('P1D'));
}
echo "\n";

$test_date = new DateTime('2024-12-20 00:00:00');
for ($i = 0; $i < 31; $i++) {
	$r = fix_year("M", "02", "2024", $test_date);
	echo "M02-2025 -> " . $r . "\n";
	// add a day to the test date
	$test_date->add(new DateInterval('P1D'));
}
echo "\n";

// ---- ----

$test_date = new DateTime('2024-12-20 00:00:00');
for ($i = 0; $i < 31; $i++) {
	$r = fix_year("W", "51", "2024", $test_date);
	echo "W51-2024 -> " . $r . "\n";
	// add a day to the test date
	$test_date->add(new DateInterval('P1D'));
}
echo "\n";

$test_date = new DateTime('2024-12-20 00:00:00');
for ($i = 0; $i < 31; $i++) {
	$r = fix_year("W", "52", "2024", $test_date);
	echo "W52-2024 -> " . $r . "\n";
	// add a day to the test date
	$test_date->add(new DateInterval('P1D'));
}
echo "\n";

$test_date = new DateTime('2024-12-20 00:00:00');
for ($i = 0; $i < 31; $i++) {
	$r = fix_year("W", "01", "2024", $test_date);
	echo "W01-2024 -> " . $r . "\n";
	// add a day to the test date
	$test_date->add(new DateInterval('P1D'));
}
echo "\n";

$test_date = new DateTime('2024-12-20 00:00:00');
for ($i = 0; $i < 31; $i++) {
	$r = fix_year("W", "02", "2024", $test_date);
	echo "W02-2024 -> " . $r . "\n";
	// add a day to the test date
	$test_date->add(new DateInterval('P1D'));
}
echo "\n";

// ----

$test_date = new DateTime('2024-12-20 00:00:00');
for ($i = 0; $i < 31; $i++) {
	$r = fix_year("W", "51", "2025", $test_date);
	echo "W51-2025 -> " . $r . "\n";
	// add a day to the test date
	$test_date->add(new DateInterval('P1D'));
}
echo "\n";

$test_date = new DateTime('2024-12-20 00:00:00');
for ($i = 0; $i < 31; $i++) {
	$r = fix_year("W", "52", "2025", $test_date);
	echo "W52-2025 -> " . $r . "\n";
	// add a day to the test date
	$test_date->add(new DateInterval('P1D'));
}
echo "\n";

$test_date = new DateTime('2024-12-20 00:00:00');
for ($i = 0; $i < 31; $i++) {
	$r = fix_year("W", "01", "2025", $test_date);
	echo "W01-2025 -> " . $r . "\n";
	// add a day to the test date
	$test_date->add(new DateInterval('P1D'));
}
echo "\n";

$test_date = new DateTime('2024-12-20 00:00:00');
for ($i = 0; $i < 31; $i++) {
	$r = fix_year("W", "02", "2025", $test_date);
	echo "W02-2025 -> " . $r . "\n";
	// add a day to the test date
	$test_date->add(new DateInterval('P1D'));
}
echo "\n";
}


test_fix_year();


?>
