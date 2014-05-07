<html>
<head>
<title>TEST: Create database</title>
</head>
<body>
<?php

$sql_script = array(
'DROP TABLE Awards',

'CREATE TABLE Awards'
.'	('
.'	AwardID     COUNTER,'
.'	AwardName   VARCHAR (50),'
.'	AwardTypeID INTEGER,'
.'	ClassID     INTEGER,'
.'	RankID      INTEGER,'
.'	RacerID     INTEGER,'
.'	Sort        INTEGER'
.'	)',

'CREATE UNIQUE INDEX PrimaryKey'
.'	ON Awards (AwardID)',
'CREATE INDEX award_id'
.'	ON Awards (AwardID)',
'CREATE INDEX AwardTypeID'
.'	ON Awards (AwardTypeID)',
'CREATE INDEX AwardTypesAwards'
.'	ON Awards (AwardTypeID)',
'CREATE INDEX ClassID'
.'	ON Awards (AwardName)',
'CREATE INDEX ClassID1'
.'	ON Awards (ClassID)',
'CREATE INDEX RankID'
.'	ON Awards (RankID)',
'CREATE INDEX Sort'
.'	ON Awards (Sort)',

'DROP TABLE AwardTypes',

'CREATE TABLE AwardTypes'
.'	('
.'	AwardTypeID COUNTER,'
.'	AwardType   VARCHAR (15)'
.'	)',

'CREATE UNIQUE INDEX PrimaryKey'
.'	ON AwardTypes (AwardTypeID)',



'DROP TABLE CheckinAudit',

'CREATE TABLE CheckinAudit'
.'	('
.'	seq    COUNTER,'
.'	stmt   VARCHAR (250),'
.'	tstamp DATETIME'
.'	)',



'DROP TABLE Classes',

'CREATE TABLE Classes'
.'	('
.'	ClassID COUNTER,'
.'	Class   VARCHAR (15)'
.'	)',

'CREATE UNIQUE INDEX Class'
.'	ON Classes (Class)',

'CREATE UNIQUE INDEX PrimaryKey'
.'	ON Classes (ClassID)',

'CREATE INDEX ID'
.'	ON Classes (ClassID)',



'DROP TABLE RaceChart',

'CREATE TABLE RaceChart'
.'	('
.'	ResultID    COUNTER,'
.'	ClassID     INTEGER,'
.'	RoundID     INTEGER,'
.'	Heat        INTEGER,'
.'	Lane        INTEGER,'
.'	RacerID     INTEGER,'
.'	ChartNumber INTEGER,'
.'	FinishTime  DOUBLE,'
.'	FinishPlace INTEGER,'
.'	Points      INTEGER,'
.'	Completed   DATETIME,'
.'	IgnoreTime  BIT,'
.'	MasterHeat  INTEGER'
.'	)',

'CREATE UNIQUE INDEX PrimaryKey'
.'	ON RaceChart (ResultID)',

'CREATE INDEX RaceChart_RacerID'
.'	ON RaceChart (RacerID)',

//'CREATE INDEX RaceChart_ClassID'
//.'	ON RaceChart (ClassID)',

'CREATE INDEX ChartNumber'
.'	ON RaceChart (ChartNumber)',

'CREATE INDEX ClassID'
.'	ON RaceChart (ClassID)',

'CREATE INDEX FinishTime'
.'	ON RaceChart (FinishTime)',

'CREATE INDEX Heat'
.'	ON RaceChart (Heat)',

'CREATE INDEX lane_number'
.'	ON RaceChart (Lane)',

'CREATE INDEX MasterHeat'
.'	ON RaceChart (MasterHeat)',

'CREATE INDEX Points'
.'	ON RaceChart (Points)',

'CREATE INDEX RacerID'
.'	ON RaceChart (RacerID)',

'CREATE INDEX RoundID'
.'	ON RaceChart (RoundID)',

'CREATE INDEX RoundsRaceChart'
.'	ON RaceChart (RoundID)',



'DROP TABLE RaceInfo',

'CREATE TABLE RaceInfo'
.'	('
.'	RaceInfoID COUNTER,'
.'	ItemKey    VARCHAR (20),'
.'	ItemValue  VARCHAR (50)'
.'	)',

'CREATE UNIQUE INDEX PrimaryKey'
.'	ON RaceInfo (RaceInfoID)',

'CREATE INDEX Code'
.'	ON RaceInfo (ItemKey)',

'CREATE INDEX ID'
.'	ON RaceInfo (RaceInfoID)',



'DROP TABLE Ranks',

'CREATE TABLE Ranks'
.'	('
.'	RankID  COUNTER,'
.'	Rank    VARCHAR (15),'
.'	ClassID INTEGER'
.'	)',

'CREATE UNIQUE INDEX PrimaryKey'
.'	ON Ranks (RankID)',

//'CREATE INDEX Ranks_ClassID'
//.'	ON Ranks (ClassID)',

'CREATE INDEX ClassID'
.'	ON Ranks (ClassID)',

'CREATE INDEX ID'
.'	ON Ranks (RankID)',

'CREATE INDEX RanksRank'
.'	ON Ranks (Rank)',




'DROP TABLE RegistrationInfo',

'CREATE TABLE RegistrationInfo'
.'	('
.'	RacerID          COUNTER,'
.'	CarNumber        INTEGER,'
.'	CarName          VARCHAR (20),'
.'	LastName         VARCHAR (20),'
.'	FirstName        VARCHAR (15),'
.'	ClassID          INTEGER,'
.'	RankID           INTEGER,'
.'	PassedInspection BIT,'
.'	ImageFile        VARCHAR (255),'
.'	Exclude          BIT'
.'	)',

'CREATE UNIQUE INDEX PrimaryKey'
.'	ON RegistrationInfo (RacerID)',

//'CREATE INDEX RegistrationInfo_ClassID'
//.'	ON RegistrationInfo (ClassID)',

//'CREATE INDEX RegistrationInfo_RankID'
//.'	ON RegistrationInfo (RankID)',

'CREATE INDEX CarNumber'
.'	ON RegistrationInfo (CarNumber)',

'CREATE INDEX ClassID'
.'	ON RegistrationInfo (ClassID)',

'CREATE INDEX Exclude'
.'	ON RegistrationInfo (Exclude)',

'CREATE INDEX LastName'
.'	ON RegistrationInfo (LastName)',

'CREATE INDEX PassedInspection'
.'	ON RegistrationInfo (PassedInspection)',

'CREATE INDEX RacerID'
.'	ON RegistrationInfo (RacerID)',

'CREATE INDEX RankID'
.'	ON RegistrationInfo (RankID)',


'DROP TABLE Roster',

'CREATE TABLE Roster'
.'	('
.'	RosterID      COUNTER,'
.'	RoundID       INTEGER,'
.'	ClassID       INTEGER,'
.'	RacerID       INTEGER,'
.'	Finalist      BIT,'
.'	GrandFinalist BIT'
.'	)',

'CREATE UNIQUE INDEX PrimaryKey'
.'	ON Roster (RosterID)',

//'CREATE INDEX {1D4487F5-B792-4DE1-BC19-37E72D8F1705}'
//.'	ON Roster (RacerID)',

//'CREATE INDEX {B7991724-2CA8-4272-BD0F-F76C04541457}'
//.'	ON Roster (RoundID)',

'CREATE INDEX ClassID'
.'	ON Roster (ClassID)',

'CREATE INDEX RacerID'
.'	ON Roster (RacerID)',

'CREATE INDEX RoundID'
.'	ON Roster (RoundID)',

'CREATE INDEX ScheduleID'
.'	ON Roster (RosterID)',



'DROP TABLE Rounds',

'CREATE TABLE Rounds'
.'	('
.'	RoundID   COUNTER,'
.'	Round     INTEGER,'
.'	ClassID   INTEGER,'
.'	ChartType INTEGER,'
.'	Phase     INTEGER'
.'	)',

'CREATE UNIQUE INDEX PrimaryKey'
.'	ON Rounds (RoundID)',

//'CREATE INDEX {BAF81037-D72B-466C-BCA9-8C845F7259E7}'
//.'	ON Rounds (ClassID)',

'CREATE INDEX ClassID'
.'	ON Rounds (ClassID)',

'CREATE INDEX Round'
.'	ON Rounds (Round)',

'CREATE INDEX RoundID'
.'	ON Rounds (RoundID)',

// --- Populate basic data

"INSERT INTO AwardTypes (AwardTypeID, AwardType) VALUES (1, 'Design General')",
"INSERT INTO AwardTypes (AwardTypeID, AwardType) VALUES (2, 'Speed General')",
"INSERT INTO AwardTypes (AwardTypeID, AwardType) VALUES (3, 'Other')",
"INSERT INTO AwardTypes (AwardTypeID, AwardType) VALUES (4, 'Design Trophy')",
"INSERT INTO AwardTypes (AwardTypeID, AwardType) VALUES (5, 'Speed Trophy')",

"INSERT INTO Classes (ClassID, Class) VALUES (1, '1-Tiger')",
"INSERT INTO Classes (ClassID, Class) VALUES (2, '2-Wolf')",
"INSERT INTO Classes (ClassID, Class) VALUES (3, '3-Bear')",
"INSERT INTO Classes (ClassID, Class) VALUES (4, '4-Webelos')",
"INSERT INTO Classes (ClassID, Class) VALUES (5, '5-Friends')",

"INSERT INTO Ranks (RankID, Rank, ClassID) VALUES (1, '1-Tiger', 1)",
"INSERT INTO Ranks (RankID, Rank, ClassID) VALUES (2, '2-Wolf', 2)",
"INSERT INTO Ranks (RankID, Rank, ClassID) VALUES (3, '3-Bear', 3)",
"INSERT INTO Ranks (RankID, Rank, ClassID) VALUES (4, '4-Webelos', 4)",
"INSERT INTO Ranks (RankID, Rank, ClassID) VALUES (5, '5-Friends', 5)",

// Effectively: there's a Round 1 for every class
"INSERT INTO Rounds (RoundID, Round, ClassID, ChartType, Phase) VALUES(1, 1, 1, 0,0)",
"INSERT INTO Rounds (RoundID, Round, ClassID, ChartType, Phase) VALUES(2, 1, 2, 0,0)",
"INSERT INTO Rounds (RoundID, Round, ClassID, ChartType, Phase) VALUES(3, 1, 3, 0,0)",
"INSERT INTO Rounds (RoundID, Round, ClassID, ChartType, Phase) VALUES(4, 1, 4, 0,0)",
"INSERT INTO Rounds (RoundID, Round, ClassID, ChartType, Phase) VALUES(5, 1, 5, 0,0)",

"INSERT INTO RaceInfo (RaceInfoID, ItemKey, ItemValue) VALUES (1, 'H1', 'Title1')",
"INSERT INTO RaceInfo (RaceInfoID, ItemKey, ItemValue) VALUES (2, 'H2', 'Title2')",
"INSERT INTO RaceInfo (RaceInfoID, ItemKey, ItemValue) VALUES (4, 'ClassID', '7')",
"INSERT INTO RaceInfo (RaceInfoID, ItemKey, ItemValue) VALUES (5, 'RoundID', '7')",
"INSERT INTO RaceInfo (RaceInfoID, ItemKey, ItemValue) VALUES (6, 'Heat', '1')",
"INSERT INTO RaceInfo (RaceInfoID, ItemKey, ItemValue) VALUES (12, 'StepDataFile', 'Complete')",
"INSERT INTO RaceInfo (RaceInfoID, ItemKey, ItemValue) VALUES (13, 'StepSWSetup', 'Incomplete')",
"INSERT INTO RaceInfo (RaceInfoID, ItemKey, ItemValue) VALUES (14, 'StepReportsSetup', 'Incomplete')",
"INSERT INTO RaceInfo (RaceInfoID, ItemKey, ItemValue) VALUES (15, 'StepHWSetup', 'Complete')",
"INSERT INTO RaceInfo (RaceInfoID, ItemKey, ItemValue) VALUES (16, 'StepDefineAwards', 'Incomplete')",
"INSERT INTO RaceInfo (RaceInfoID, ItemKey, ItemValue) VALUES (17, 'StepRegistration', 'Incomplete')",
"INSERT INTO RaceInfo (RaceInfoID, ItemKey, ItemValue) VALUES (18, 'StepSchedules', 'Incomplete')",
"INSERT INTO RaceInfo (RaceInfoID, ItemKey, ItemValue) VALUES (19, 'StepRacing', 'Incomplete')",
"INSERT INTO RaceInfo (RaceInfoID, ItemKey, ItemValue) VALUES (20, 'StepAwardsCeremony', 'Incomplete')",
"INSERT INTO RaceInfo (RaceInfoID, ItemKey, ItemValue) VALUES (21, 'StepOrgSetup', 'Complete')",

"INSERT INTO RaceInfo (RaceInfoID, ItemKey, ItemValue) VALUES (25, 'xbs-award', 'Exclusively By Scout')"

);

try {
#	$db = new PDO('mysql:dbname=pack140-2013;host=127.0.0.1', 'root', 'myrootsql');
  $db = new PDO('odbc:DSN=gprm;Exclusive=NO','','');
	$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

	if (false) {
	  $rs = $db->query('SELECT COUNT(*) FROM RegistrationInfo');
	  $result = $rs->fetch();
	  // Should be empty:
	  if ($result[0] > 0) {
		echo '<h2>RegistrationInfo table is not empty!</h2>';
		echo '</body></html>';
		exit(1);
	  }
	  $rs->closeCursor();
	  // Get a new connection, because the old one will keep a lock on
	  // RegistrationInfo.  No way to close the old one explicitly?
	  $db = new PDO('odbc:DSN=gprm;Exclusive=NO','','');
	}

	foreach ($sql_script as $stmt) {
	  echo '<p>Executing '.$stmt.'</p>'."\n";
	  $db->exec($stmt);
	}
	echo '<h2>Database script completed!</h2>';

} catch (Exception $e) {
	echo 'Exception: '.$e;
}

?>
</body>
</html>
