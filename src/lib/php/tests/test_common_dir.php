<?php
/*
 Copyright (C) 2011 Hewlett-Packard Development Company, L.P.

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
 */

/**
 * \file test_common_dir.php
 * \brief unit tests for common-dir.php
 */

require_once('../common-dir.php');

/**
 * \class test_common_dir
 */
class test_common_dir extends PHPUnit_Framework_TestCase
{
  /* initialization */
  protected function setUp()
  {
    //print "Starting unit test for common-parm.php\n";
  }

  /**
   * \brief test for Isdir Isartifact Iscontainer
   */
  function test_Is()
  {
    print "Starting unit test for common-dir.php\n";
    print "test function Isdir()\n";
    $mode = 536888320;
    $result = Isdir($mode);
    $this->assertEquals(true, $result);
    $mode = 33188;
    $result = Isdir($mode);
    $this->assertEquals(false, $result);
    print "test function Isartifact()\n";
    $mode = 536888320;
    $result = Isartifact($mode);
    $this->assertEquals(false, $result);
    $mode = 805323776;
    $result = Isartifact($mode);
    $this->assertEquals(true, $result);
    print "test function Iscontainer()\n";
    $mode = 536888320;
    $result = Iscontainer($mode);
    $this->assertEquals(true, $result);
    $mode = 805323776;
    $result = Iscontainer($mode);
    $this->assertEquals(true, $result);

    print "test function DirMode2String()\n";
    $result = DirMode2String($mode);
    $this->assertEquals("a-d-----S---", $result);
    //print "Ending unit test for common-dir.php\n";
  }
  /**
   * \brief test for Bytes2Human
   */
  function test_Bytes2Human()
  {
    print "test function Bytes2Human()\n";
    $Bytes = 1073741824;
    $result = Bytes2Human($Bytes);
    $this->assertEquals("1 GB", $result);
    $Bytes = 10240;
    $result = Bytes2Human($Bytes);
    $this->assertEquals("10 KB", $result);
  }

  /**
   * \brief clean the env
   */
  protected function tearDown() {
    //print "Ending unit test for common-parm.php\n";
  }
}

?>
