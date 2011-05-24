This file is based on the URL module from Moodle - http://moodle.org/

Moodle is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

Moodle is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

copyright 2009 Petr Skoda (http://skodak.org), 2011 The University of Nottingham (Chris Greenhalgh)
license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later


WebCTImport module
==================

This module is based on a clone of the MOODLE_20_STABLE URL module and provides an interface
to import files from a custom dump of the WebCT database, for the University of Nottingham.

The WebCT Database export code is in https://github.com/cgreenhalgh/webctdbexport
It consists of a folder/file hierarchy on disk which mirrors the JSON-encoded information 
used by the Moodle Repository API, i.e. a list of Files/sub-Folders.

This modules is intended to:

- provide a user interaction entry point to allow a user to start adding files/folders/links
  from the webct dump

- add moodle URL items for each link, label items for each folder, and webctimport items for each
  file.

- each webctimport item represents the need to import an actual file, and can be processed in 
  the background. For UoN the plan is to move the file into Equella and replace the webctimport
  item with an equella item. Alternatively it could be loaded into Moodle and replaced with a
  File item.

Chris Greenhalgh, 2011-07-11
  
TO DO
=====

- implement it all...

