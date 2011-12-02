#!/usr/bin/php
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
 * \file pkgConfig.php
 * \brief prepare a system for install testing.
 *
 *  pkgConfig prepares a system for the installation of fossology packages and
 *  installs the fossology packages.
 *
 *  @param string $fossVersion the version of fossology to install?
 *  @todo what should the api for this really be?
 *
 * @version "$Id $"
 * Created on Jul 19, 2011 by Mark Donohoe
 */

require_once '../lib/TestRun.php';

global $Debian;
global $RedHat;

$debian = NULL;
$redHat = NULL;
$fedora = NULL;
$ubuntu = NULL;

/*
 * determine what os and version:
 * configure yum or apt for fossology
 * install fossology
 * stop scheduler (if install is good).
 * do the steps below.
 * 1. tune kernel
 * 2. postgres files
 * 3. php ini files
 * 4. fossology.org apache file
 * 5. checkout fossology
 * 6. run fo-installdeps
 * 7. for RHEL what else?
 */

// Check for Super User
$euid = posix_getuid();
if($euid != 0) {
  print "Error, this script must be run as root\n";
  exit(1);
}

// determine os flavor
$distros = array();
$f = exec('cat /etc/issue', $dist, $dRtn);
$distros = explode(' ', $dist[0]);

//list($distro, , , , ,) = explode(' ', $dist[0]);
echo "DB: distros[0] is:{$distros[0]}\n";

/*
 #cat /etc/passwd | grep "$find_user" | cut -c":" -f6
 if(array_key_exists('HOME', $_ENV))
 {
 $home = $_ENV['HOME'];
 }
 else
 {
 // look in /etc/passwd
 $cmd = ''
 $home = exec
 }
 */
// configure for fossology and install fossology packages.
// Note that after this point, you could just stop if one could rely on the install
// process to give a valid exit code... but it would still be good to bring up
// the system and do some small uploads.

// stop scheduler
// config the system
/*
 * 1. tune kernel
 * 2. postgres files
 * 3. php ini files
 * 4. fossology.org apache file  (this should have been done by the install....)
 * 7. for RHEL what else?
 */

// create this class which can be used by any release/os
$testUtils = new TestRun();
// distro can be Debian, Red, Fedora, Ubuntu
switch ($distros[0]) {
  case 'Debian':
    $debian = TRUE;  // is this needed?
    $debianVersion = $distros[2];
    echo "debian version is:$debianVersion\n";
    try
    {
      $Debian = new ConfigSys($distros[0], $debianVersion);
    }
    catch (Exception $e)
    {
      echo "FATAL! could not process ini file for Debian $debianVersion system\n";
      break;
    }

    if(insertDeb($Debian) === FALSE)
    {
      echo "FATAL! cannot insert deb line into /etc/apt/sources.list\n";
      break;
    }
    // install fossology
    echo "DB: installing fossology\n";
    if(!installFossology($Debian))
    {
      echo "FATAL! Could not install fossology on {$distros[0]} version $debianVersion\n";
    }
    echo "DB: stopping scheduler\n";
    // Stop scheduler so system files can be configured.
    $testUtils->stopScheduler();
    echo "DB: calling tuning kernel\n";
    tuneKernel();
    echo "DB: config files\n";
    if(configDebian($distros[0], $debianVersion) === FALSE)
    {
      echo "FATAL! could not configure postgres or php config files\n";
      exit(1);
    }
    echo "DB: checking apache config\n";
    if(!configApache2($distros[0]))
    {
      echo "Fatal, could not configure apache2 to use fossology\n";
    }

    // mini test
    break;
  case 'Red':
    $redHat = 'RedHat';
    $rhVersion = $distros[6];
    echo "rh version is:$rhVersion\n";
    //echo "distros looks like:\n";print_r($distros) . "\n";
    /*
    * 1. process ini
    * 2. configure yum
    * 3. install fossology
    * 4. configure rest of system.
    */
    try
    {
      $RedHat = new ConfigSys($redHat, $rhVersion);
    }
    catch (Exception $e)
    {
      echo "FATAL! could not process ini file for RedHat $rhVersion system\n";
      echo $e;
      break;
    }
    $RedHat->printAttr();
    if(!configYum())
    {
      echo "FATAL! could not install fossology.conf yum configuration file\n";
      break;
    }
    if(!installFossology($RedHat))
    {
      echo "FATAL! Could not install fossology on $redHat version $rhVersion\n";
    }
    break;
  case 'Fedora':
    $fedora = 'Fedora';
    break;
  case 'Ubuntu':
    $distro = 'Ubuntu';
    $ubunVersion = $distros[1];
    echo "Ubuntu version is:$ubunVersion\n";
    echo "DB: calling configsys\n";
    try
    {
      $Ubuntu = new ConfigSys($distros[0], $ubunVersion);
    }
    catch (Exception $e)
    {
      echo "FATAL! could not process ini file for Ubuntu $ubunVersion system\n";
      echo $e . "\n";
      break;
    }
    echo "DB: inserting deb\n";
    if(insertDeb($Ubuntu) === FALSE)
    {
      echo "FATAL! cannot insert deb line into /etc/apt/sources.list\n";
      break;
    }
    // install fossology
    echo "DB: installing fossology\n";
    if(!installFossology($Ubuntu))
    {
      echo "FATAL! Could not install fossology on {$distros[0]} version $ubunVersion\n";
    }
    echo "DB: stopping scheduler\n";
    // Stop scheduler so system files can be configured.
    $testUtils->stopScheduler();
    echo "DB: calling tuning kernel\n";
    tuneKernel();
    echo "DB: config files\n";
    if(configDebian($distros[0], $ubunVersion) === FALSE)
    {
      echo "FATAL! could not configure postgres or php config files\n";
      exit(1);
    }
    echo "DB: checking apache config\n";
    if(!configApache2($distros[0]))
    {
      echo "Fatal, could not configure apache2 to use fossology\n";
    }

    // mini test
    break;
  default:
    echo "Fatal! unrecognized distrobution! {$distros[0]}\n" ;
    exit(1);
    break;
}
class ConfigSys {

  public $osFlavor;
  public $osVersion = 0;
  private $fossVersion;
  private $osCodeName;
  public $deb;
  public $comment = '';
  public $yum;

  function __construct($osFlavor, $osVersion)
  {
    if(empty($osFlavor))
    {
      throw new Exception("No Os Flavor supplied\n");
    }
    if(empty($osVersion))
    {
      throw new Exception("No Os Version Supplied\n");
    }

    $dataFile = '../dataFiles/pkginstall/' . strtolower($osFlavor) . '.ini';
    $releases = parse_ini_file($dataFile, 1);
    //echo "DB: the parsed ini file is:\n";
    //print_r($releases) . "\n";
    foreach($releases as $release => $values)
    {
      if($values['osversion'] == $osVersion)
      {
        // found the correct os, gather attributes
        $this->osFlavor = $values['osflavor'];
        $this->osVersion =  $values['osversion'];
        $this->fossVersion =  $values['fossversion'];
        $this->osCodeName =  $values['codename'];
        // code below is needed to avoid php notice
        switch (strtolower($this->osFlavor)) {
          case 'ubuntu':
          case 'debian':
            $this->deb =  $values['deb'];
            break;
          case 'fedora':
          case 'redhat':
            $this->yum = $values['yum'];
            break;
          default:
            ;
            break;
        }
        $this->comment = $values['comment'];
      }
    }
    if($this->osVersion == 0)
    {
      throw new Exception("FATAL! no matching os flavor or version found\n");
    }
    return;
  } // __construct

  /**
   * prints all the classes attributes (properties)
   *
   * @return void
   */
  public function printAttr()
  {

    echo "Attributes of ConfigSys:\n";
    echo "\tosFlavor:$this->osFlavor\n";
    echo "\tosVersion:$this->osVersion\n";
    echo "\tfossVersion:$this->fossVersion\n";
    echo "\tosCodeName:$this->osCodeName\n";
    echo "\tdeb:$this->deb\n";
    echo "\tcomment:$this->comment\n";
    echo "\tyum:$this->yum\n";

    return;
  } //printAttr
} // ConfigSys

/**
 * \brief insert the fossology debian line in /etc/apt/sources.list
 *
 * @param object $objRef the object with the deb attribute
 *
 * @return boolean
 */
function insertDeb($objRef)
{

  if(!is_object($objRef))
  {
    return(FALSE);
  }
  // open file for append
  $APT = fopen('/etc/apt/sources.list', 'a+');
  if(!is_resource($APT))
  {
    echo "FATAL! could not open /etc/apt/sources.list for modification\n";
    return(FALSE);
  }
  $written = fwrite($APT, "\n");
  fflush($APT);

  if(empty($objRef->comment))
  {
    $comment = '# Automatically inserted by pkgConfig.php';
  }

  $com = fwrite($APT, $objRef->comment . "\n");
  if(!$written = fwrite($APT, $objRef->deb))
  {
    echo "FATAL! could not write deb line to /etc/apt/sources.list\n";
    return(FALSE);
  }
  fclose($APT);
  return(TRUE);
}  // insertDeb

/**
 * \brief Install fossology using either apt or yum
 *
 * installFossology assumes that the correct configuration for yum and the
 * correct fossology version has been configured into the system.
 *
 * @param object $objRef an object reference (should be to ConfigSys)
 *
 * @return boolean
 */
function installFossology($objRef)
{
  if(!is_object($objRef))
  {
    return(FALSE);
  }
  $aptUpdate = 'sudo apt-get update 2>&1';
  $aptInstall = 'sudo apt-get -y --force-yes install fossology 2>&1';
  $yumUpdate = 'sudo yum -y update 2>&1';
  $yumInstall = 'sudo yum -y install fossology > fossinstall.log 2>&1';
  //$yumInstall = 'sudo yum -y install fossology';

  switch ($objRef->osFlavor) {
    case 'Ubuntu':
    case 'Debian':
      $last = exec($aptUpdate, $out, $rtn);
      //echo "last is:$last\nresults of update are:\n";print_r($out) . "\n";
      $last = exec($aptInstall, $iOut, $iRtn);
      if($iRtn != 0)
      {
        echo "Failed to install fossology!\nTranscript is:\n";
        echo implode("\n",$iOut) . "\n";
        return(FALSE);
      }
      break;
    case 'Fedora':
    case 'RedHat':
      echo "running yum update\n";
      $last = exec($yumUpdate, $out, $rtn);
      if($rtn != 0)
      {
        echo "Failed to update yum repositories with fossology!\nTranscript is:\n";
        echo implode("\n",$out) . "\n";
        return(FALSE);
      }
      echo "running yum install fossology\n";
      $last = exec($yumInstall, $yumOut, $yumRtn);
      //echo "install of fossology finished, yumRtn is:$yumRtn\nlast is:$last\n";
      //$clast = system('cat fossinstall.log');
      if($yumRtn != 0)
      {
        echo "Failed to install fossology!\nTranscript is:\n";
        $clast = system('cat fossinstall.log');
        return(FALSE);
      }
      break;

    default:
      echo "FATAL! Unrecongnized OS/Release, not one of Ubuntu, Debian, RedHat" .
      " or Fedora\n";
      return(FALSE);
      break;
  }
  return(TRUE);
}

/**
 * \brief copyFiles, copy one or more files to the destination,
 * throws exception if file is not copied.
 *
 * The method can be used to rename a single file, but not a directory.  It
 * cannot rename multiple files.
 *
 * @param mixed $file the file to copy (string), use an array for multiple files.
 * @param string $dest the destination path (must exist, must be writable).
 *
 * @retrun boolean
 *
 */
function copyFiles($files, $dest)
{
  if(empty($files))
  {
    throw new Exception('No file to copy', 0);
  }
  if(empty($dest))
  {
    throw new Exception('No destination for copy', 0);
  }
  if(is_array($files))
  {
    foreach($files as $file)
    {
      // Get left name and check if dest is a directory, copy cannot copy to a
      // dir.
      $baseFile = basename($file);
      if(is_dir($dest))
      {
        $to = $dest . "/$baseFile";
      }
      else
      {
        $to = $dest;
      }
      if(!copy($file, $to))
      {
        throw new Exception("Could not copy $file to $to");
      }
    }
  }
  else
  {
    $baseFile = basename($files);
    if(is_dir($dest))
    {
      $to = $dest . "/$baseFile";
    }
    else
    {
      $to = $dest;
    }
    if(!copy($files,$to))
    {
      throw new Exception("Could not copy $file to $to");
    }
  }
  return(TRUE);
} // copyFiles


/**
 * \brief find the version of postgres and return major release and sub release.
 * For example, if postgres is at 8.4.8, this function will return 8.4.
 *
 * @return boolean
 */
function findVerPsql()
{
  $version = NULL;

  $last = exec('psql --version', $out, $rtn);
  if($rtn != 0)
  {
    return(FALSE);
  }
  else
  {
    // isolate the version number and return it
    list( , ,$ver) = explode(' ', $out[0]);
    $version = substr($ver, 0, 3);
  }
  return($version);
}

/**
 * \brief tune the kernel for this boot and successive boots
 *
 * returns void
 */
function tuneKernel()
{
  // check to see if we have already done this... so the sysctl.conf file doesn't
  // end up with dup entries.
  $grepCmd = 'grep shmmax=512000000 /etc/sysctl.conf /dev/null 2>&1';
  $last = exec($grepCmd, $out, $rtn);
  if($rtn == 0)   // kernel already configured
  {
    echo "DB: already configured, returning\n";
    return;
  }
  $cmd1 = "echo 512000000 > /proc/sys/kernel/shmmax";
  $cmd2 = "echo 'kernel.shmmax=512000000' >> /etc/sysctl.conf";
  // Tune the kernel
  $last1 = exec($cmd1, $cmd1Out, $rtn1);
  if ($rtn1 != 0)
  {
    echo "Fatal! Could not set kernel shmmax in /proc/sys/kernel/shmmax\n";
  }
  $last2 = exec($cmd2, $cmd2Out, $rtn2);
  // make it permanent
  if ($rtn2 != 0)
  {
    echo "Fatal! Could not turn kernel.shmmax in /etc/sysctl.conf\n";
  }
  return;
} // tuneKernel

/**
 * \brief check to see if fossology is configured into apache.  If not copy the
 * config file and configure it.  Restart apache if configured.
 *
 * @param string $osType type of the os, e.g. Debian, Ubuntu, Red, Fedora
 *
 * @return boolean
 */

function configApache2($osType)
{
  if(empty($osType))
  {
    return(FALSE);
  }
  switch ($osType) {
    case 'Ubuntu':
    case 'Debian':
      if(is_link('/etc/apache2/conf.d/fossology'))
      {
        break;
      }
      else
      {
        // copy config file, create sym link
        if(!copy('../dataFiles/pkginstall/fo-apache.conf', '/etc/fossology/fo-apache.conf'))
        {
          echo "FATAL!, Cannot configure fossology into apache2\n";
          return(FALSE);
        }
        if(!symlink('/etc/fossology/fo-apache.conf','/etc/apache2/conf.d/fossology'))
        {
          echo "FATAL! Could not create symlink in /etc/apache2/conf.d/ for fossology\n";
          return(FALSE);
        }
      }
      break;
    case 'Red':
      ;
      break;
    default:
      ;
      break;
  }
  // restart apapche so changes take effect
  if(!restart('apache2'))
  {
    echo "Erorr! Could not restart apache2, please restart by hand\n";
    return(FALSE);
  }
  return(TRUE);
} // configApache2

/**
 * \brief config a debian based system to install fossology.
 *
 * copy postgres, php config files so that fossology can run.
 *
 * @param string $osType either Debian or Ubuntu
 * @param string $osVersion the particular version to install
 *
 * @return boolean
 */
function configDebian($osType, $osVersion)
{
  if(empty($osType))
  {
    return(FALSE);
  }
  if(empty($osVersion))
  {
    return(FALSE);
  }

  // based on type read the appropriate ini file.

  echo "DB:configD: osType is:$osType\n";
  echo "DB:configD: osversion is:$osVersion\n";
  
  // can't check in pg_hba.conf as it shows HP's firewall settings, get it
  // internally
  
  $wcmd = "wget -q -O ../dataFiles/pkginstall/debian/6/pg_hba.conf " .
    "http://fonightly.usa.hp.com/testfiles/pg_hba.conf ";
  
  //echo "DB: wcmd is:\n$wcmd\n";

  $last = exec($wcmd, $wOut, $wRtn);
  if($wRtn != 0)
  {
    echo "Error, could not download pg_hba.conf file, pleases configure by hand\n";
    echo "wgetoutput is:\n";print_r($wOut) . "\n";
  }
  
  $debPath = '../dataFiles/pkginstall/debian/6/';

  $psqlFiles = array(
          $debPath . 'pg_hba.conf',
          $debPath . 'postgresql.conf');

  switch ($osVersion)
  {
    case '6.0':
      echo "debianConfig got os version 6.0!\n";
      // copy config files
      /*
      * Change the structure of data files:
      * e.g. debian/5/pg_hba..., etc, all files that go with this version
      *      debian/6/pg_hba....
      *      and use a symlink for the 'codename' squeeze -> debian/6/
      */
      try
      {
        copyFiles($psqlFiles, "/etc/postgresql/8.4/main");
      }
      catch (Exception $e)
      {
        echo "Failure: Could not copy postgres 8.4 config file\n";
      }
      try
      {
        copyFiles($debPath . 'cli-php.ini', '/etc/php5/cli/php.ini');
      } catch (Exception $e)
      {
        echo "Failure: Could not copy php.ini to /etc/php5/cli/php.ini\n";
        return(FALSE);
      }
      try
      {
        copyFiles($debPath . 'apache2-php.ini', '/etc/php5/apache2/php.ini');
      } catch (Exception $e)
      {
        echo "Failure: Could not copy php.ini to /etc/php5/apache2/php.ini\n";
        return(FALSE);
      }
      break;
    case '10.04.3':
      echo "DB: in 10.04.3\n";
      try
      {
        copyFiles($psqlFiles, "/etc/postgresql/8.4/main");
      }
      catch (Exception $e)
      {
        echo "Failure: Could not copy postgres 8.4 config file\n";
      }
      try
      {
        copyFiles($debPath . 'cli-php.ini', '/etc/php5/cli/php.ini');
      } catch (Exception $e)
      {
        echo "Failure: Could not copy php.ini to /etc/php5/cli/php.ini\n";
        return(FALSE);
      }
      try
      {
        copyFiles($debPath . 'apache2-php.ini', '/etc/php5/apache2/php.ini');
      } catch (Exception $e)
      {
        echo "Failure: Could not copy php.ini to /etc/php5/apache2/php.ini\n";
        return(FALSE);
      }
      break;
    default:
      return(FALSE);     // unsupported debian version
      break;
  }
  // restart apache and postgres so changes take effect
  if(!restart('apache2'))
  {
    echo "Erorr! Could not restart apache2, please restart by hand\n";
    return(FALSE);
  }
  // Get the postrgres version so the correct file is used.
  $ver = findVerPsql();
  echo "DB: returned version is:$ver\n";
  $pName = 'postgresql-' . $ver;
  echo "DB pName is:$pName\n";
  if(!restart($pName))
  {
    echo "Erorr! Could not restart $pName, please restart by hand\n";
    return(FALSE);
  }
  return(TRUE);
}  // configDebian

/**
 * \brief config yum on a redhat based system to install fossology.
 *
 * Copies the Yum configuration file for fossology to
 *
 * @param string $osType the type of redhat system, e.g. RedHat or Fedora
 * @param string $osVersion
 *
 * @return boolean
 */
function configYum()
{
  global $RedHat;

  echo "DB: configYUM: yum line is:$RedHat->yum\n";

  if(empty($RedHat->yum))
  {
    echo "FATAL, no yum config file to install\n";
    return(FALSE);
  }

  // coe plays with yum stuff, check if yum.repos.d exists and if not create it.
  if(is_dir('/etc/yum.repos.d'))
  {
    copyFiles("../dataFiles/pkginstall/$RedHat->yum", '/etc/yum.repos.d/fossology.conf');
  }
  else
  {
    // create the dir and then copy
    if(!mkdir('/etc/yum.repos.d'))
    {
      echo "FATAL! could not create yum.repos.d\n";
      return(FALSE);
    }
    copyFiles($RedHat->yum, '/etc/yum.repos.d/fossology.conf');
  }
  return(TRUE);
}  // configYum

/**
 * \brief restart the application passed in, so any config changes will take
 * affect.  Assumes application is restartable via /etc/init.d/<script>.
 * The application passed in should match the script name in /etc/init.d
 *
 * @param string $application the application to restart. The application passed
 *  in should match the script name in /etc/init.d
 *
 *  @return boolen
 */
function restart($application)
{
  if(empty($application))
  {
    return(FALSE);
  }

  $last = exec("/etc/init.d/$application restart 2>&1", $out, $rtn);
  if($rtn != 0)
  {
    echo "FATAL! could not restart $application\n";
    echo "transcript is:\n";print_r($out) . "\n";
    return(FALSE);
  }
  return(TRUE);
} // restart
?>