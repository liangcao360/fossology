#!/usr/bin/php
<?php
/*
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
 */

/**
 * schema-export
 * \brief export the fossology schema to a file
 * 
 * @param string $Filename path to file
 * 
 * @version "$Id$"
 */
global $GlobalReady;
$GlobalReady = 1;

//require_once (dirname(__FILE__)) . '/../share/fossology/php/pathinclude.php';
require_once '/usr/local/share/fossology/php/pathinclude.php';

global $LIBEXECDIR;
require_once "$LIBEXECDIR/libschema.php";

global $PGCONN;
global $Name;

$Name = basename($argv[0]);
$usage = "Usage: " . basename($argv[0]) . " [options]
  -f <filepath> pathname to schema data file
  -h this help usage";

$Options = getopt('f:h');
if (empty($Options))
{
	print "$usage\n";
	exit(1);
}

if (array_key_exists('h',$Options))
{
	print "$usage\n";
	exit(0);
}

if (array_key_exists('f', $Options))
{
	$Filename = $Options['f'];
}
if((strlen($Filename)) == 0)
{
	print "Error, no filename supplied\n$usage\n";
	exit(1);
}

// get db params and open connection to db.

echo "connecting to db fossology-gold\n";
$dbOptions = 'host=sirius.ostt dbname=fossology-gold user=fossy password=fossy';
$PGCONN = dbConnect($dbOptions);

echo "schemaEXDB: calling ExportSchema with filename:$Filename\n";

$res = ExportSchema($Filename);

/**
 * ExportSchema
 * \brief Export the current schema to a file.
 * 
 * @param string $Filename path to the file to store the schema in.
 * 
 * @return
*/

function ExportSchema($Filename = NULL) {
	
	global $Name;
	global $PGCONN;
	
	if (empty($Filename)) {
		$Filename = Filename;
	}
	$Schema = GetSchema();
	$Fout = fopen($Filename, "w");
	if (!$Fout) {
		return ("Failed to write to $Filename\n");
	}
	fwrite($Fout, "<?php\n");
	fwrite($Fout, "/* This file is generated by " . $Name . " */\n");
	fwrite($Fout, "/* Do not manually edit this file */\n\n");
	fwrite($Fout, "  global \$GlobalReady;\n");
	fwrite($Fout, "  if (!isset(\$GlobalReady)) { exit; }\n\n");
	fwrite($Fout, "  global \$DATADIR;\n\n");
	fwrite($Fout, "  print \"datadir is:\$DATADIR\\n\";\n\n");
	fwrite($Fout, '  $Schema=array();' . "\n\n");
	foreach($Schema as $K1 => $V1) {
		$K1 = str_replace('"', '\"', $K1);
		$A1 = '  $Schema["' . $K1 . "\"]";
		if (!is_array($V1)) {
			$V1 = str_replace('"', '\"', $V1);
			fwrite($Fout, "$A1 = \"$V1\";\n");
		}
		else {
			foreach($V1 as $K2 => $V2) {
				$K2 = str_replace('"', '\"', $K2);
				$A2 = $A1 . '["' . $K2 . '"]';
				if (!is_array($V2)) {
					$V2 = str_replace('"', '\"', $V2);
					fwrite($Fout, "$A2 = \"$V2\";\n");
				}
				else {
					foreach($V2 as $K3 => $V3) {
						$K3 = str_replace('"', '\"', $K3);
						$A3 = $A2 . '["' . $K3 . '"]';
						if (!is_array($V3)) {
							$V3 = str_replace('"', '\"', $V3);
							fwrite($Fout, "$A3 = \"$V3\";\n");
						}
						else {
							foreach($V3 as $K4 => $V4) {
								$V4 = str_replace('"', '\"', $V4);
								$A4 = $A3 . '["' . $K4 . '"]';
								fwrite($Fout, "$A4 = \"$V4\";\n");
							} /* K4 */
							fwrite($Fout, "\n");
						}
					} /* K3 */
					fwrite($Fout, "\n");
				}
			} /* K2 */
			fwrite($Fout, "\n");
		}
	} /* K1 */
	fwrite($Fout, "?>\n");
	fclose($Fout);
	print "Data written to $Filename\n";
	return;
} // ExportSchema()
?>
