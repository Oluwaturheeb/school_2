<?php
require_once "Autoload.php";

if(!Session::check("user"))
	Redirect::to("/login");
else 
	$title = "My dashboard";

$user = Session::get("user");
$v = new Validate();

if ($_GET) {
	$v->val_req(["number" => true]);

	if ($v->error()) 
		Redirect::to();

	$user = $v->fetch("a_id");
	$title = "Student report";
}

require_once "inc/header.php";
$e = new Easy();

// getting the current session

$e->table("event");
$cc = $e->fetch(["content", "type"])->exec();

if ($e->count()) {
	foreach ($cc as $key) {
		if ($key->type == "session") 
			$c_ses = $key->content;
		elseif ($key->type == "result")
			$date = $key->content;
	}
	Session::set("session", $c_ses);
} else {
	$date = null;
	$c_ses = "Session unavailable";
}


if (isset($_GET["a_id"])){
$e->table(["student", "auth"]);
	$r = $e->rfetch(
		["student.*", "email"], 
		[["a_id", "auth.id"]],
		)
		->exec(1);
} else {
	$e->table("student");
	$r = $e->fetch(
		["*"] ,
		["a_id", $user]
		)
		->exec(1);
}
Session::set("class", $r->class);
Session::set("dept", $r->dept);
Session::set("active", $r->active);

if ($r->active) 
	$active = "Welcome <b>$r->first $r->last</b>, you can only view and print your result since you are no longer a part of the system!";
else 
	$active = Utils::time() . " -- " . $c_ses;
?>

		<div class="col-12 mt-3">
			<div class="header">
				<?php
				if (Session::get("level") < 3) {
				$admin = "<div>
					<div>$r->pre. $r->p_first $r->p_last (Parent/Guardian)</div>
					<div class='icon'>
						<a href='mailto:$r->email'><img src='assets/img/email.png'></a>
						<a href='tel:$r->phone'><img src='assets/img/phone.svg'></a>
					</div>
					<div>$r->email</div>
					<div>$r->dob ($r->age)</div>
					<div>$r->hadd</div>
				</div>";
				} else {
					$admin = "";
				}
				echo <<<__here
				<img src="$r->picture" alt="$r->first" class="img-thumbnail">
				<div>
					<b>$r->first $r->last</b>
					$admin
					<small>$r->class</small>
					<small><i>$r->dept class</i></small>
				</div>
			</div>
__here; ?>
			<div class="bl mt-4 px-3"><i><?php echo $active ?></i></div>
		</div>

		<?php
		$e->table("score");
		$sc = $e->fetch(["*"], ["a_id", $user])->exec();

		// $subject = [];

		$th = $td = $rep = $cur = $trr = $tr = "";

		// all available class to avoid undefined var error!
		$j1 = $j2 = $j3 = $s1 = $s2 = "";

		foreach($sc as $ss) {
				$data = Utils::djson($ss->data);
				// $data =
				for ($i = 0, $count = count(array_reverse($data)); $i < $count; $i++) {
					$session = $ss->session;
					$th .= "<th>{$data[$i]["subject"]}</th>";
					$td .= "<td>{$data[$i]["score"]}</td>";
				}

				$thh = "<thead><th>Session</th> {$th}</thead>";
				$th = "";
				
				$tr .= "<tr><td> {$session} </td> {$td} </tr>";
				$td = "";

				// listing the results from the current class and previous classes

				if($ss->class == $r->class) {
					// checking if we can display the result for this session

					if (!empty($date) && $date !== date("Y-m-d")) 
						$tr = "<tr><td>$ss->session</td><td colspan='4'>Result for this session is witheld by the Adminstrator's Department</td></tr>";
					
					$trr .= $tr;
					$cur = <<<__here
					<div class="h2 my-3">$r->class <small>(Current class)</small></div>
					<table class="table table-stripe">
						<thead>
							$thh
						<tbody>
							$trr
						</tbody>
					</table>
__here;
					$tr = "";
				} else {
					if ($ss->class == "Jss 1") {
						$trr .= $tr;
						$j1 = <<<__here
						<div class="h2 my-3">$ss->class</div>
						<details>
							<summary>See results</summary>
							<table class="table table-stripe">
								$thh
								<tbody>
									$trr
								</tbody>
							</table>
						</details>
__here;
					} elseif ($ss->class == "Jss 2") {
						$trr .= $tr;
						$j2 = <<<__here
						<div class="h2 my-3">$ss->class</div>
						<details>
							<summary>See results</summary>
							<table class="table table-stripe">
								$thh
								<tbody>
									$trr
								</tbody>
							</table>
						</details>
__here;
					} elseif ($ss->class == "Jss 3") {
						$trr .= $tr;
						$j3 = <<<__here
						<div class="h2 my-3">$ss->class</div>
						<details>
							<summary>See results</summary>
							<table class="table table-stripe">
								$thh
								<tbody>
									$trr
								</tbody>
							</table>
						</details>
__here;
					} elseif ($ss->class == "Sss 1") {
						$trr .= $tr;
						$s1 = <<<__here
						<div class="h2 my-3">$ss->class</div>
						<details>
							<summary>See results</summary>
							<table class="table table-stripe">
								$thh
								<tbody>
									$trr
								</tbody>
							</table>
						</details>
__here;
					}elseif ($ss->class == "Sss 2") {
						$trr .= $tr;
						$s2 = <<<__here
						<div class="h2 my-3">$ss->class</div>
						<details>
							<summary>See results</summary>
							<table class="table table-stripe">
								$thh
								<tbody>
									$trr
								</tbody>
							</table>
						</details>
__here;
					}
					// i ran out of logic here so i wanted to try something if its gonna work

					if ($ss->session == "Third term") {
						$trr = "";
					}
					$tr = "";

				}
			}
			$report = $cur . $s2 . $s1 . $j3 . $j2 . $j1;
		?>

		<div class="col-12 col-sm-8 mt-5">
			<div class="action">
				<div class="report">
					<h2>Report</h2>
					<?php echo $report; ?>
				</div>
			</div>
		</div>
<?php
require_once "inc/footer.php";