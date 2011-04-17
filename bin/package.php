<?php
error_reporting(E_ALL & ~E_STRICT & ~E_DEPRECATED);
require_once('PEAR/PackageFileManager2.php');
PEAR::setErrorHandling(PEAR_ERROR_DIE);

function createPackager($original_file, $options = array())
{
  // merge the options with these defaults.
  $options = array_merge(array(
    'packagefile'       => 'package.xml',
    'filelistgenerator' => 'file',
    'simpleoutput'      => true,
    'baseinstalldir'    => '/DocBlox',
    'packagedirectory'  => dirname(__FILE__).'/../',
    'clearcontents'     => true,
    'ignore'            => array(
      'build/*',
      'data/output/*',
      'bin/package.php',
      'src/Image/*',      // Dependency with a PEAR package
      'src/Zend/*',       // Dependency with a PEAR package
      'src/markdown/*',   // Dependency with a PEAR package
      'src/markdown.php'  // Dependency with a PEAR package
    ),
    'exceptions'        => array(
      'bin/docblox.php'  => 'script',
      'docblox.dist.xml' => 'php',
      'LICENSE'          => 'php',
      'phpunit.xml.dist' => 'php',
      'README'           => 'php',
    ),
    'installexceptions' => array(
      'bin/docblox.php' => '/',
      'bin/docblox.bat' => '/'
    ),
    'dir_roles'         => array(
      'bin'   => 'php',
      'docs'  => 'php',
      'data'  => 'php',
      'tests' => 'php',
      'src'   => 'php',
    ),
  ), $options);

  $packagexml = PEAR_PackageFileManager2::importOptions($original_file, $options);
  $packagexml->setPackageType('php');

  $packagexml->setPackage('DocBlox');
  $packagexml->setSummary('PHP 5.3 compatible API Documentation generator aimed at projects of all sizes and Continuous Integration');
  $packagexml->setDescription(<<<DESC
DocBlox is a Documentation Generation Application (DGA) for use with PHP applications.

It is capable of transforming the comments in your source code into a full API reference document.

DocBlox is build to be PHP 5.3 compatible, fast, having a low memory consumption and easily integratable into Continuous Integration.
DESC
  );
  $packagexml->setChannel('pear.docblox-project.org');
  $packagexml->setNotes('Please see the CHANGELOG in the root of the application for the latest changes');

  $packagexml->setPhpDep('5.2.4');
  $packagexml->setPearinstallerDep('1.4.0');
  $packagexml->addPackageDepWithChannel('required', 'PEAR', 'pear.php.net', '1.4.0');
  $packagexml->addPackageDepWithChannel('required', 'Image_GraphViz', 'pear.php.net', '1.3.0');
  $packagexml->addPackageDepWithChannel('required', 'zf', 'pear.zfcampus.org', '1.11.3');
  $packagexml->addPackageDepWithChannel('required', 'MarkdownExtra', 'pear.michelf.com', '1.2.4');

  $packagexml->addReplacement('bin/docblox.bat', 'pear-config', '/usr/bin/env php', 'php_bin');
  $packagexml->addReplacement('bin/docblox.bat', 'pear-config', '@php_bin@', 'php_bin');
  $packagexml->addReplacement('bin/docblox.bat', 'pear-config', '@php_dir@', 'php_dir');

  $packagexml->addReplacement('bin/docblox.php', 'pear-config', '/usr/bin/env php', 'php_bin');
  $packagexml->addReplacement('bin/docblox.php', 'pear-config', '@php_bin@', 'php_bin');
  $packagexml->addReplacement('bin/docblox.php', 'pear-config', '@php_dir@', 'php_dir');

  $packagexml->addMaintainer('lead', 'mvriel', 'Mike van Riel', 'mike.vanriel@naenius.com');
  $packagexml->setLicense('MIT', 'http://www.opensource.org/licenses/mit-license.html');

  // Add this as a release, and generate XML content
  $packagexml->addRelease();
  $packagexml->setOSInstallCondition('windows');
  $packagexml->addInstallAs('bin/docblox.bat', 'docblox.bat');

  $packagexml->addRelease();
  $packagexml->addInstallAs('bin/docblox.php', 'docblox');
  $packagexml->addIgnoreToRelease('bin/docblox.bat');

  return $packagexml;
}


echo 'DocBlox PEAR Packager v1.0'.PHP_EOL;

if ($argc < 3)
{
  echo <<<HELP

Usage:
  php package.php [version] [stability] [make|nothing]

Description:
  The DocBlox packager generates a package.xml file and accompanying package.
  By specifying the version and stability you can tell the packager which version to package.

  A file will only be generated if the third parameter is the word 'make'; otherwise the output will be send to
  the command line.

HELP;
  exit(0);
}

$packager = createPackager('../package.xml');

$packager->setAPIVersion($argv[1]);
$packager->setReleaseVersion($argv[1]);
$packager->setReleaseStability($argv[2]);
$packager->setAPIStability($argv[2]);

$packager->generateContents();
if (isset($argv[3]) && ($argv[3] == 'make'))
{
  $packager->writePackageFile();
}
else
{
  $packager->debugPackageFile();
}
