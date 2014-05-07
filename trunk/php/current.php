<?php session_start(); ?>
<?php
require_once('data.inc');

// This script produces XML in response to a request from a page that wants to 
// update itself as the race progresses.  In particular, it supports ondeck.php (showing 
// race results by heat, as well as the schedule for future heats) and racer-results.php
// (showing race results organized by racer).
//
// The updates that each of these pages has to track comes in different flavors:
//
// (1) The heat that's "current" advances.
// (2) New heat results (race times) have been recorded since the last update.
// (3) New heats have been scheduled for a racing round that we already knew about.
//     (That is, there was an entry in the Rounds table, even if the heats hadn't 
//     yet been scheduled.)
// (4) New racing rounds have been scheduled (new entries in Rounds table).
//
// Deletions are also possible, but relatively rare.  We don't worry about these, and
// instead rely on updates being triggered by new, replacement data.
//
// This script returns an XML document that looks like:
//
// <update_summary>
//   <current_heat classid="{classid}"
//                 roundid="{roundid}"
//                 round="{round}"
//                 group="{}"
//                 heat="{heat number}"
//     >{class name}</class>
//   <lanes n="{nlanes}"/>
//   <options update-period="{period in ms}" kiosk-page="(kiosk page)" use-master-sched="(0 or 1)"/>
//
//   <update resultid="{resultid}" time="{finishtime, as n.nnn}"/>
//   <update resultid="{resultid}" time="{finishtime, as n.nnn}"/>
//   ...
//
//   <has_new_schedule roundid="{roundid}" group="{groupid}"/>
//   ...
//
//   <high_water roundid= resultid= completed= />
// </update_summary>
//
// The class, round, and heat elements identify the "current" heat.
//
// The update elements provide heat times for heats that completed after the page's last update.
// The client-side page, via update.js, passes a date/time parameter named 'since' to identify
// the high water mark of Completed values that it's already displaying.
//
// The most_recent element tells the page what its new high water mark for Completed values is.
//
// The final_round element gives a high water mark for RoundIDs.  When this value changes, relative
// to what the page already knows about, it gives up on incrementality and just reloads the whole page.

// date/time string of last update time (high-water completed)
// Format: '2013-11-14 14:57:14'
$since = $_GET['since'];
$hwresultid = $_GET['high_water_resultid'];
if (!$hwresultid) $hwresultid = 0;

$now_running = get_running_round();
$use_master_sched = use_master_sched();

header('Content-Type: text/xml');
?>
<update_summary>
<current_heat classid="<?php echo @$now_running['classid']; ?>"
              roundid="<?php echo @$now_running['roundid']; ?>"
              round="<?php echo @$now_running['round']; ?>"
              group="<?php echo $use_master_sched ? @$now_running['round'] : @$now_running['roundid']; ?>"
              heat="<?php echo @$now_running['heat']; ?>"><?php if (!$use_master_sched) { echo @$now_running['class']; } ?></current_heat>

  <lanes n="<?php echo get_lane_count(); ?>"/>
  <options update-period="<?php echo update_period(); ?>"
           kiosk-page="<?php echo kiosk_page(); ?>"
           use-master-sched="<?php echo use_master_sched(); ?>"/>
  <?php

  # TODO: CDate is Access-specific			  
  $sql = 'SELECT'
    .' lane, resultid, finishtime, completed'
    .' FROM Rounds'
    .' INNER JOIN RaceChart'
    .' ON RaceChart.roundid = Rounds.roundid'
    .' WHERE finishtime IS NOT NULL'
    .($since ? " AND completed > CDate('".$since."')" : "")
    .' ORDER BY completed, resultid, lane';
  $stmt = $db->query($sql);

  if ($stmt === FALSE) {
	$info = $db->errorInfo();
    echo '<error msg="'.$info[2].'" query="'.$sql.'"/>'."\n";
  }

  // <update> elements for heats recorded since the ['since']
  $max_time = $since;
  foreach ($stmt as $rs) {
    echo "<update resultid='".$rs['resultid']."'"
      ." time='".$rs['finishtime']
      ."'/>\n";
    if ($rs['completed'] > $max_time)
      $max_time = $rs['completed'];
  }

  // <has_new_schedule> elements for rounds affected by newly-entered racechart rows

  $sql = 'SELECT DISTINCT rounds.roundid, round '
    .' FROM racechart'
    .' INNER JOIN rounds'
    .' ON racechart.roundid = rounds.roundid'
    .' WHERE resultid > '.$hwresultid
    .' ORDER BY rounds.roundid';
  $stmt = $db->query($sql);
  if ($stmt === FALSE) {
	$info = $db->errorInfo();
    echo '<error msg="'.$info[2].'" query="'.$sql.'"/>'."\n";
  }

  foreach ($stmt as $rs) {
    echo '<has_new_schedule roundid="'.$rs['roundid']
      .'" groupid="'.$rs[$use_master_sched ? 'round' : 'roundid'].'"/>'."\n";
  }

  $high_water_rounds = high_water_rounds();
  ?>
  <high_water roundid="<?php echo $high_water_rounds['roundid'] ?>"
              round="<?php echo $high_water_rounds['round'] ?>"
              resultid="<?php echo high_water_resultid(); ?>"
              completed="<?php echo $max_time; ?>" />

</update_summary>
