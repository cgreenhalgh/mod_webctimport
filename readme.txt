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

CHANGES
=======

- 2011-10-10: add resource redirects to webctimport index, which attempts to import 
  resources one by one. 

- 2011-10-10: replaced window.parent.document.location=... parent redirects with javascript
  function call that should work on IE8 (as well as other browsers).

- 2011-10-03: fixed some outstanding bugs that broke use with IE8 and/or Moodle 2.1.
  removed most debugging output.

- 2011-08-01: modified to use latest webctdbexport file format, i.e. separate user area,
  files in common SHA-1 pool with file.json redirect.

- 2011-08-03: modified tables to only required fields; first implementation of
  import to moodle file.

  
TO DO
=====

- implement worker timeout and error retry in import
- when creating moodle file use owner user context, not current user context.
- check permissions in (a) treeviewsubmit (b) get_listing (c) get_file (d)
  get_rawfile.
- add support for exporting TOC items from webct (modules)
- add support for importing TOC items
- allow a set of files (e.g.) a directory to be imported as a mini-site
  (creates one moodle file or equella item)
- disable import as equella while unimplemented
- implement import to equella ?
- allow import to file to be limited by file size (setting)
- change tree view form encoding/submission to not depend on input name
  encoding
- add un/select of children to tree view
- tidy up formatting of tree view, e.g. Expand button height
- add icons to tree view
- ? show hints about already import items in tree view
- for equella import, use already imported item, if any (scope by module?!)


- implement facility to convert moodle file (resource) items to equella item
  with import/search.

