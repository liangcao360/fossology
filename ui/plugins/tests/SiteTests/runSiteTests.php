#!/usr/bin/php
<?php
/***********************************************************
 Copyright (C) 2008 Hewlett-Packard Development Company, L.P.

 This program is free software; you can redistribute it and/or
 modify it under the terms of the GNU General Public License
 version 2 as published by the Free Software Foundation.

 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License along
 with this program; if not, write to the Free Software Foundation, Inc.,
 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
***********************************************************/
/*
 * Runner script that runs the web tests
 */
// set the path for where simpletest is
$path = '/usr/share/php' . PATH_SEPARATOR;
set_include_path(get_include_path() . PATH_SEPARATOR . $path);

/* simpletest includes */
require_once '/usr/local/simpletest/unit_tester.php';
require_once '/usr/local/simpletest/web_tester.php';
require_once '/usr/local/simpletest/reporter.php';
require_once ('../../../../tests/TestEnvironment.php');

global $URL;
global $USER;
global $PASSWORD;

$test = &new TestSuite('Fossology Repo Site UI tests');
//$test->addTestFile('AboutMenuTest.php');
//$test->addTestFile('login.php');
//$test->addTestFile('SearchMenuTest.php');
//$test->addTestFile('OrgFoldersMenuTest-Create.php');
//$test->addTestFile('OrgFoldersMenuTest-Delete.php');
//$test->addTestFile('OrgFoldersMenuTest-Edit.php');
//$test->addTestFile('OrgFoldersMenuTest-Move.php');
//$test->addTestFile('OrgUploadsMenuTest-Delete.php');
//$test->addTestFile('OrgUploadsMenuTest-Move.php');
//$test->addTestFile('OrgUploadsMenuTest-RemoveLA.php');
//$test->addTestFile('OrgLicenseMenuTest-DGroups.php');
//$test->addTestFile('OrgLicenseMenuTest-DTerms.php');
//$test->addTestFile('OrgLicenseMenuTest-MGroups.php');
//$test->addTestFile('OrgLicenseMenuTest-MTerms.php');
$test->addTestFile('UploadInstructMenuTest.php');
$test->addTestFile('UploadFileMenuTest.php');
$test->addTestFile('UploadServerMenuTest.php');
$test->addTestFile('UploadUrlMenuTest.php');
$test->addTestFile('UploadOne-ShotMenuTest.php');
if (TextReporter::inCli())
{
  exit ($test->run(new TextReporter()) ? 0 : 1);
}
$test->run(new HtmlReporter());
?>