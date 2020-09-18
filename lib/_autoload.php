<?php
/**
 * Load all PSR-4 classes and interfaces from src/ recursively. 
 * 
 * PSR-0/Pear use cases may not be prepared to autoload PSR-4 files from src/ dir
 * Also, it is not sufficient to force load only the direct relative of the PSR-0 files.
 * 
 * There is no forward relation from the PSR-4 classes to their PSR-0 interfaces.
 * But there is a backward relation from all PSR-0 interfaces to corresponding namespaced 
 * PSR-4 src/ entities.
 * 
 * This file is kept a low-tech as possible to facilitate autogeneration and keep overhead low
 * 
 */
$srcDir = dirname(__FILE__, 2) . '/src';
require_once("$srcDir/Binder.php");
require_once("$srcDir/DependencyFinder.php");
require_once("$srcDir/Exception.php");
require_once("$srcDir/Scope.php");
require_once("$srcDir/Injector.php");
require_once("$srcDir/Binder/AnnotatedSetters.php");
require_once("$srcDir/TopLevel.php");
require_once("$srcDir/Binder/Closure.php");
require_once("$srcDir/Binder/Factory.php");
require_once("$srcDir/Binder/Implementation.php");
